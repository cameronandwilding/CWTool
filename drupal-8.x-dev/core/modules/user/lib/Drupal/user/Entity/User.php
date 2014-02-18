<?php

/**
 * @file
 * Definition of Drupal\user\Entity\User.
 */

namespace Drupal\user\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Field\FieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the user entity class.
 *
 * @EntityType(
 *   id = "user",
 *   label = @Translation("User"),
 *   controllers = {
 *     "storage" = "Drupal\user\UserStorageController",
 *     "access" = "Drupal\user\UserAccessController",
 *     "list" = "Drupal\user\Controller\UserListController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\user\ProfileFormController",
 *       "cancel" = "Drupal\user\Form\UserCancelForm",
 *       "register" = "Drupal\user\RegisterFormController"
 *     },
 *     "translation" = "Drupal\user\ProfileTranslationController"
 *   },
 *   admin_permission = "administer user",
 *   base_table = "users",
 *   uri_callback = "user_uri",
 *   label_callback = "user_format_name",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "uid",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "user.view",
 *     "edit-form" = "user.edit",
 *     "admin-form" = "user.account_settings",
 *     "cancel-form" = "user.cancel"
 *   }
 * )
 */
class User extends ContentEntityBase implements UserInterface {

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('uid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isNew() {
    return !empty($this->enforceIsNew) || $this->id() === NULL;
  }

  /**
   * {@inheritdoc}
   */
  static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);

    if (!isset($values['created'])) {
      $values['created'] = REQUEST_TIME;
    }
    // Users always have the authenticated user role.
    $values['roles'][] = DRUPAL_AUTHENTICATED_RID;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    // Update the user password if it has changed.
    if ($this->isNew() || ($this->pass->value && $this->pass->value != $this->original->pass->value)) {
      // Allow alternate password hashing schemes.
      $this->pass->value = \Drupal::service('password')->hash(trim($this->pass->value));
      // Abort if the hashing failed and returned FALSE.
      if (!$this->pass->value) {
        throw new EntityMalformedException('The entity does not have a password.');
      }
    }

    if (!$this->isNew()) {
      // If the password is empty, that means it was not changed, so use the
      // original password.
      if (empty($this->pass->value)) {
        $this->pass->value = $this->original->pass->value;
      }
    }

    // Store account cancellation information.
    foreach (array('user_cancel_method', 'user_cancel_notify') as $key) {
      if (isset($this->{$key})) {
        \Drupal::service('user.data')->set('user', $this->id(), substr($key, 5), $this->{$key});
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if ($update) {
      // If the password has been changed, delete all open sessions for the
      // user and recreate the current one.
      if ($this->pass->value != $this->original->pass->value) {
        drupal_session_destroy_uid($this->id());
        if ($this->id() == $GLOBALS['user']->id()) {
          drupal_session_regenerate();
        }
      }

      // Update user roles if changed.
      if ($this->getRoles() != $this->original->getRoles()) {
        $storage_controller->deleteUserRoles(array($this->id()));
        $storage_controller->saveRoles($this);
      }

      // If the user was blocked, delete the user's sessions to force a logout.
      if ($this->original->status->value != $this->status->value && $this->status->value == 0) {
        drupal_session_destroy_uid($this->id());
      }

      // Send emails after we have the new user object.
      if ($this->status->value != $this->original->status->value) {
        // The user's status is changing; conditionally send notification email.
        $op = $this->status->value == 1 ? 'status_activated' : 'status_blocked';
        _user_mail_notify($op, $this);
      }
    }
    else {
      // Save user roles.
      if (count($this->getRoles()) > 1) {
        $storage_controller->saveRoles($this);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);

    $uids = array_keys($entities);
    \Drupal::service('user.data')->delete(NULL, $uids);
    $storage_controller->deleteUserRoles($uids);
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles() {
    $roles = array();
    foreach ($this->get('roles') as $role) {
      $roles[] = $role->value;
    }
    return $roles;
  }

  /**
   * {@inheritdoc}
   */
  public function getSecureSessionId() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSessionData() {
    return array();
  }
  /**
   * {@inheritdoc}
   */
  public function getSessionId() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRole($rid) {
    return in_array($rid, $this->getRoles());
  }

  /**
   * {@inheritdoc}
   */
  public function addRole($rid) {
    $roles = $this->getRoles();
    $roles[] = $rid;
    $this->set('roles', array_unique($roles));
  }

  /**
   * {@inheritdoc}
   */
  public function removeRole($rid) {
    $this->set('roles', array_diff($this->getRoles(), array($rid)));
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission) {
    // User #1 has all privileges.
    if ((int) $this->id() === 1) {
      return TRUE;
    }

    $roles = \Drupal::entityManager()->getStorageController('user_role')->loadMultiple($this->getRoles());

    foreach ($roles as $role) {
      if ($role->hasPermission($permission)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPassword() {
    return $this->get('pass')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPassword($password) {
    $this->get('pass')->value = $password;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmail($mail) {
    $this->get('mail')->value = $mail;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSignature() {
    return $this->get('signature')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSignatureFormat() {
    return $this->get('signature_format')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAccessedTime() {
    return $this->get('access')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAccessTime($timestamp) {
    $this->get('access')->value = $timestamp;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastLoginTime() {
    return $this->get('login')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastLoginTime($timestamp) {
    $this->get('login')->value = $timestamp;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return $this->get('status')->value == 1;
  }

  /**
   * {@inheritdoc}
   */
  public function isBlocked() {
    return $this->get('status')->value == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function activate() {
    $this->get('status')->value = 1;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function block() {
    $this->get('status')->value = 0;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeZone() {
    return $this->get('timezone')->value;
  }

  /**
   * {@inheritdoc}
   */
  function getPreferredLangcode($default = NULL) {
    $language_list = language_list();
    $preferred_langcode = $this->get('preferred_langcode')->value;
    if (!empty($preferred_langcode) && isset($language_list[$preferred_langcode])) {
      return $language_list[$preferred_langcode]->id;
    }
    else {
      return $default ? $default : language_default()->id;
    }
  }

  /**
   * {@inheritdoc}
   */
  function getPreferredAdminLangcode($default = NULL) {
    $language_list = language_list();
    $preferred_langcode = $this->get('preferred_admin_langcode')->value;
    if (!empty($preferred_langcode) && isset($language_list[$preferred_langcode])) {
      return $language_list[$preferred_langcode]->id;
    }
    else {
      return $default ? $default : language_default()->id;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInitialEmail() {
    return $this->get('init')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isAuthenticated() {
    return $this->id() > 0;
  }
  /**
   * {@inheritdoc}
   */
  public function isAnonymous() {
    return $this->id() == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsername() {
    $name = $this->get('name')->value ?: \Drupal::config('user.settings')->get('anonymous');
    \Drupal::moduleHandler()->alter('user_format_name', $name, $this);
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function setUsername($username) {
    $this->set('name', $username);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $fields['uid'] = FieldDefinition::create('integer')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The user UUID.'))
      ->setReadOnly(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The user language code.'));

    $fields['preferred_langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Preferred admin language code'))
      ->setDescription(t("The user's preferred language code for receiving emails and viewing the site."));

    $fields['preferred_admin_langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Preferred language code'))
      ->setDescription(t("The user's preferred language code for viewing administration pages."));

    // The name should not vary per language. The username is the visual
    // identifier for a user and needs to be consistent in all languages.
    $fields['name'] = FieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of this user.'))
      ->setSetting('default_value', '')
      ->setPropertyConstraints('value', array(
        // No Length constraint here because the UserName constraint also covers
        // that.
        'UserName' => array(),
        'UserNameUnique' => array(),
      ));

    $fields['pass'] = FieldDefinition::create('string')
      ->setLabel(t('Password'))
      ->setDescription(t('The password of this user (hashed).'));

    $fields['mail'] = FieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email of this user.'))
      ->setSetting('default_value', '')
      ->setPropertyConstraints('value', array('UserMailUnique' => array()));

    // @todo Convert to a text field in https://drupal.org/node/1548204.
    $fields['signature'] = FieldDefinition::create('string')
      ->setLabel(t('Signature'))
      ->setDescription(t('The signature of this user.'));
    $fields['signature_format'] = FieldDefinition::create('string')
      ->setLabel(t('Signature format'))
      ->setDescription(t('The signature format of this user.'));

    $fields['timezone'] = FieldDefinition::create('string')
      ->setLabel(t('Timezone'))
      ->setDescription(t('The timezone of this user.'))
      ->setSetting('max_length', 32);

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('User status'))
      ->setDescription(t('Whether the user is active (1) or blocked (0).'))
      ->setSetting('default_value', 1);

    // @todo Convert to a "created" field in https://drupal.org/node/2145103.
    $fields['created'] = FieldDefinition::create('integer')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the user was created.'));

    // @todo Convert to a "timestamp" field in https://drupal.org/node/2145103.
    $fields['access'] = FieldDefinition::create('integer')
      ->setLabel(t('Last access'))
      ->setDescription(t('The time that the user last accessed the site.'))
      ->setSetting('default_value', 0);

    // @todo Convert to a "timestamp" field in https://drupal.org/node/2145103.
    $fields['login'] = FieldDefinition::create('integer')
      ->setLabel(t('Last login'))
      ->setDescription(t('The time that the user last logged in.'))
      ->setSetting('default_value', 0);

    $fields['init'] = FieldDefinition::create('email')
      ->setLabel(t('Initial email'))
      ->setDescription(t('The email address used for initial account creation.'))
      ->setSetting('default_value', '');

    // @todo Convert this to entity_reference_field, see
    // https://drupal.org/node/2044859.
    $fields['roles'] = FieldDefinition::create('string')
      ->setLabel(t('Roles'))
      ->setDescription(t('The roles the user has.'));

    return $fields;
  }

}
