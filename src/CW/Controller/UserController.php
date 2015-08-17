<?php
/**
 * @file
 *
 * User controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;
use CW\Adapter\DrupalUserAdapter;
use CW\Exception\CWException;

/**
 * Class UserController
 * @package CW\Controller
 */
class UserController extends AbstractEntityController {

  // Flag for current user evaluation.
  // Used for marking the current user, especially when the execution is delayed
  // or scheduled and the current user of the future session is intended for use.
  const USER_CURRENT = -1;

  // Basic user IDs.
  const UID_ANONYMOUS = 0;
  const UID_ADMIN = 1;

  // User states.
  const STATE_ACTIVE = 1;
  const STATE_BLOCKED = 0;

  // Role name.
  const ROLE_AUTHENTICATED_USER = 'authenticated user';

  // Entity type.
  const TYPE_USER = 'user';

  /**
   * @var DrupalUserAdapter
   */
  private static $drupalAdapter;

  /**
   * @var bool
   */
  private $realEntityLoadedFlag = FALSE;

  /**
   * @return bool
   */
  public function isCurrent() {
    return self::currentUID() == $this->getEntityId();
  }

  /**
   * @return mixed
   */
  public static function currentUID() {
    $account = self::getDrupalAdapter()->getGlobalUserObject();
    return $account->uid;
  }

  /**
   * @return bool
   */
  public function isAdmin() {
    return $this->getEntityId() == self::UID_ADMIN;
  }

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::TYPE_USER;
  }

  /**
   * Drupal login.
   */
  public function login() {
    self::getDrupalAdapter()->login($this->entity(), $this->logger);
  }

  /**
   * @param string $roleName
   * @return bool
   */
  public function hasRole($roleName) {
    return isset($this->entity()->roles) && in_array($roleName, $this->entity()->roles);
  }

  /**
   * Check that a user has any of a given array of roles.
   *
   * @param string[] $roleNames
   * @return bool
   */
  public function hasAnyRole(array $roleNames) {
    foreach ($roleNames as $roleName) {
      if ($this->hasRole($roleName)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Check that a user has all of a given array of roles.
   *
   * @param string[] $roleNames
   * @return bool
   */
  public function hasAllRoles(array $roleNames) {
    foreach ($roleNames as $roleName) {
      if (!$this->hasRole($roleName)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * User entity exist in GLOBAL user object - but that's incomplete sometimes
   * as it does not contain fields or other properties.
   * It is "incomplete" when not loaded through the full entity load process.
   *
   * @return bool
   */
  protected function isUserEntityIncomplete() {
    $entity = parent::entity();
    return isset($entity->sid);
  }

  /**
   * {@inheritdoc}
   */
  public function entity($forceReload = self::RELOAD_IGNORE) {
    // Flag to make sure it's reloaded only once at max.
    if (!$this->realEntityLoadedFlag && $this->isUserEntityIncomplete()) {
      $this->realEntityLoadedFlag = TRUE;
      return parent::entity(self::RELOAD_FORCE);
    }

    $this->realEntityLoadedFlag = TRUE;
    return parent::entity($forceReload);
  }

  /**
   * @return mixed
   */
  public function getUsername() {
    return $this->property('name');
  }

  /**
   * @return string
   */
  public function getMail() {
    return $this->property('mail');
  }

  /**
   * Get the relative path of the user. (Not transformed.)
   * Drupal's url can turn it to the transformed version.
   *
   * @return string
   */
  public function getPath() {
    return 'user/' . $this->getEntityId();
  }

  /**
   * @return \CW\Adapter\DrupalUserAdapter
   */
  protected static function getDrupalAdapter() {
    if (empty(self::$drupalAdapter)) {
      self::$drupalAdapter = new DrupalUserAdapter();
    }

    return self::$drupalAdapter;
  }

  /**
   * @param \CW\Adapter\DrupalUserAdapter $drupalAdapter
   */
  public static function setDrupalAdapter(DrupalUserAdapter $drupalAdapter) {
    self::$drupalAdapter = $drupalAdapter;
  }

}

/**
 * @}
 */
