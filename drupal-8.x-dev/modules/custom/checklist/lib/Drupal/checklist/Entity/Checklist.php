<?php
/**
 * @file
 * Definition of Drupal\checklist\Entity\checklist.
 */
namespace Drupal\checklist\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\checklist\ChecklistInterface;
use Drupal\user\UserInterface;
use \Drupal\Core\Entity\EntityInterface;
/**
 * Defines the node entity class.
 *
 * @EntityType(
 *   id = "checklist_item",
 *   label = @Translation("Content"),
 *   bundle_label = @Translation("Content type"),
 *   controllers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\checklist\Entity\Form\ChecklistFormController",
 *       "edit" = "Drupal\checklist\Entity\Form\ChecklistFormController",
 *     },
 *     "translation" = "Drupal\node\NodeTranslationController"
 *   },
 *   base_table = "checklist_item",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "ciid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "node_type",
 *   permission_granularity = "bundle",
 *   links = {
 *     "admin-form" = "checklist.settings",
 *     "add-form" = "checklist.add",
 *   }
 * )
 */
class ChecklistItem extends ContentEntityBase implements ChecklistInterface {
  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('tid')->value;
  }

  /*
  * Overrides Drupal\Core\Entity\Entity::getRevisionId().
  */
  public function getRevisionId() {
    return $this->get('vid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    // @todo Handle this through property defaults.
    if (empty($values['created'])) {
      $values['created'] = REQUEST_TIME;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    // Before saving the node, set changed and revision times.
    $this->changed->value = REQUEST_TIME;
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageControllerInterface $storage_controller, \stdClass $record) {
    parent::preSaveRevision($storage_controller, $record);

    if ($this->newRevision) {
      // When inserting either a new node or a new node revision, $node->log
      // must be set because {node_field_revision}.log is a text column and
      // therefore cannot have a default value. However, it might not be set at
      // this point (for example, if the user submitting a node form does not
      // have permission to create revisions), so we ensure that it is at least
      // an empty string in that case.
      // @todo Make the {node_field_revision}.log column nullable so that we
      //   can remove this check.
      if (!isset($record->log)) {
        $record->log = '';
      }
    }
    elseif (isset($this->original) && (!isset($record->log) || $record->log === '')) {
      // If we are updating an existing node without adding a new revision, we
      // need to make sure $entity->log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->log = $this->original->log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    // Update the node access table for this node, but only if it is the
    // default revision. There's no need to delete existing records if the node
    // is new.
    if ($this->isDefaultRevision()) {
      \Drupal::entityManager()->getAccessController('node')->writeGrants($this, $update);
    }

    // Reindex the node when it is updated. The node is automatically indexed
    // when it is added, simply by being added to the node table.
    if ($update) {
      node_reindex_node_search($this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::preDelete($storage_controller, $entities);

    // Assure that all nodes deleted are removed from the search index.
    if (\Drupal::moduleHandler()->moduleExists('search')) {
      foreach ($entities as $entity) {
        search_reindex($entity->nid->value, 'node_search');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $nodes) {
    parent::postDelete($storage_controller, $nodes);
    \Drupal::service('node.grant_storage')->deleteNodeRecords(array_keys($nodes));
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL) {
    if ($operation == 'create') {
      return parent::access($operation, $account);
    }

    return \Drupal::entityManager()
      ->getAccessController($this->entityTypeId)
      ->access($this, $operation, $this->prepareLangcode(), $account);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareLangcode() {
    $langcode = $this->language()->id;
    // If the Language module is enabled, try to use the language from content
    // negotiation.
    if (\Drupal::moduleHandler()->moduleExists('language')) {
      // Load languages the node exists in.
      $node_translations = $this->getTranslationLanguages();
      // Load the language from content negotiation.
      $content_negotiation_langcode = \Drupal::languageManager()->getCurrentLanguage(Language::TYPE_CONTENT)->id;
      // If there is a translation available, use it.
      if (isset($node_translations[$content_negotiation_langcode])) {
        $langcode = $content_negotiation_langcode;
      }
    }
    return $langcode;
  }


  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
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
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isPromoted() {
    return (bool) $this->get('promote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPromoted($promoted) {
    $this->set('promote', $promoted ? NODE_PROMOTED : NODE_NOT_PROMOTED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isSticky() {
    return (bool) $this->get('sticky')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSticky($sticky) {
    $this->set('sticky', $sticky ? NODE_STICKY : NODE_NOT_STICKY);
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  public static function baseFieldDefinitions($entity_type) {
    $fields['ciid'] = FieldDefinition::create('integer')
      ->setLabel(t('Checklist ID'))
      ->setDescription(t('The checklist ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The checklist UUID.'))
      ->setReadOnly(TRUE);

    $fields['type'] = FieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The bundle of the Checklist entity.'))
      ->setRequired(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The checklist language code.'));

    $fields['name'] = FieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the checklist entity.'))
      ->setTranslatable(TRUE)
      ->setPropertyConstraints('value', array('Length' => array('max' => 32)));

    $fields['user_id'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The ID of the associated user.'))
      ->setSettings(array('target_type' => 'user'))
      ->setTranslatable(TRUE);

    $fields['checkbox_field'] = FieldDefinition::create('integer')
      ->setLabel(t('Checkbox'))
      ->setDescription(t('Checkbox field of the checklist entity.'));

    return $fields;
  }
}
?>