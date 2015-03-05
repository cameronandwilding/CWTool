<?php
/**
 * @file
 *
 * Abstract entity controller.
 */

namespace CW\Controller;

use CW\Factory\EntityControllerFactory;
use CW\Model\ObjectHandler;
use CW\Util\FieldUtil;
use EntityMetadataWrapper;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractEntityController
 * @package CW\Controller
 *
 * Provides data (Drupal) access to the data and suppose to keep data specific
 * behavior.
 *
 * Use entity controllers through entity controller factories so instances are
 * stored properly in the cache (identity object containers).
 * @see EntityControllerFactory
 */
abstract class AbstractEntityController {

  // On the entity, created and changed timestamps are different sometimes, even
  // if the entity was not updated. We need to check the updated state (being
  // created and changed different) outside of a threshold.
  // Eg.: $isUpdated = $entity->changed > $entity->created + UPDATE_TIMESTAMP_VALIDABILITY_THRESHOLD;
  const UPDATE_TIMESTAMP_VALIDABILITY_THRESHOLD = 5;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * Entity type.
   *
   * @var string
   */
  private $entityType;

  /**
   * Entity ID.
   *
   * @var int
   */
  private $entityId;

  /**
   * Data accessor, in order to eliminate coupling with Drupal entity API.
   * Use this for entity operations (CRUD).
   *
   * @var ObjectHandler
   */
  protected $objectHandler;

  /**
   * The entity metadata wrapper object.
   * Use $this->metadata() to access it.
   *
   * @var EntityMetadataWrapper
   */
  private $entityMetadataWrapper;

  /**
   * Drupal object.
   * Use $this->entity() to access it.
   *
   * @var object
   */
  private $entity;

  /**
   * Update flag.
   *
   * @var bool
   */
  private $isUpdated = FALSE;

  /**
   * Constructor.
   *
   * @param \CW\Model\ObjectHandler $objectLoader
   * @param \Psr\Log\LoggerInterface $logger
   * @param $entity_type
   * @param $entity_id
   */
  public function __construct(ObjectHandler $objectLoader, LoggerInterface $logger, $entity_type, $entity_id) {
    $this->logger = $logger;
    $this->entityType = $entity_type;
    $this->entityId = $entity_id;
    $this->objectHandler = $objectLoader;
  }

  /**
   * Get the entity metadata wrapper of the entity.
   *
   * @return EntityMetadataWrapper
   *
   * @throws Exception
   *  Entity metadata wrapper exception.
   */
  public function metadata() {
    if (!isset($this->entityMetadataWrapper)) {
      $this->entityMetadataWrapper = $this->objectHandler->loadMetadata($this->entityType, $this->entity());
    }

    return $this->entityMetadataWrapper;
  }

  /**
   * Get the Drupal object of the entity.
   *
   * @return mixed|object
   */
  public function entity() {
    if (!isset($this->entity)) {
      $this->entity = $this->objectHandler->loadSingleEntity($this->entityType, $this->entityId);
    }

    return $this->entity;
  }

  /**
   * Sets the Drupal object.
   *
   * @param object $entity
   *  Drupal object.
   */
  public function setEntity($entity) {
    $this->entity = $entity;
  }

  /**
   * Save data to database.
   */
  public function save() {
    $this->logger->info('Entity has been saved {this}', array('this' => $this->__toString()));
    $this->objectHandler->save($this->entityType, $this->entity());
    $this->setClean();
  }

  /**
   * Check if object has change.
   *
   * @return boolean
   */
  public function isDirty() {
    return $this->isUpdated;
  }

  /**
   * Mark as changed.
   */
  public function setDirty() {
    $this->isUpdated = TRUE;
  }

  /**
   * Mask as clean.
   */
  public function setClean() {
    $this->isUpdated = FALSE;
  }

  /**
   * Delete entity permanently.
   *
   * @return mixed
   */
  public function delete() {
    return $this->objectHandler->delete($this->entityType, $this->entityId);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '[' . get_class($this) . ", {$this->entityType}:{$this->entityId}]@" . spl_object_hash($this);
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @return int
   */
  public function getEntityId() {
    return $this->entityId;
  }

  /**
   * Get the entity type the class represents.
   * Throws exception if there is not.
   *
   * This is a helper for other services to be aware of the entity info.
   *
   * @throws \Exception
   */
  public static function getClassEntityType() {
    throw new Exception('Undefined entity type');
  }

  /**
   * Get the entity bundle the class represents.
   * Similar to:
   * @see $this->getClassEntityType()
   * @throws \Exception
   */
  public static function getClassEntityBundle() {
    throw new Exception('Undefined entity bundle');
  }

  /**
   * Extracts the exact field value.
   *
   * @param $field_name
   * @param string $key
   * @param int $idx
   * @param string $lang
   * @return null|mixed
   */
  public function fieldValue($field_name, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang][$idx][$key])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang][$idx][$key];
  }

  /**
   * Extracts a whole field item (array).
   *
   * @param $field_name
   * @param int $idx
   * @param string $lang
   * @return null
   */
  public function fieldItem($field_name, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang][$idx])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang][$idx];
  }

  /**
   * Gets all field items.
   *
   * @param $field_name
   * @param string $lang
   * @return null
   */
  public function fieldItems($field_name, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang];
  }

  /**
   * Set a single field value.
   *
   * @param $field_name
   * @param $value
   * @param string $key
   * @param int $idx
   * @param string $lang
   */
  public function setFieldValue($field_name, $value, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE) {
    $this->entity()->{$field_name}[$lang][$idx][$key] = $value;
  }

  /**
   * Get the actual entity controller object of the entity referenced by the field.
   *
   * @param $fieldName
   * @param \CW\Factory\EntityControllerFactory $factory
   * @return \CW\Controller\AbstractEntityController|null
   */
  protected function getControllerFromEntityReferenceField($fieldName, EntityControllerFactory $factory) {
    if (!($targetID = $this->fieldValue($fieldName, FieldUtil::KEY_TARGET_ID))) {
      return NULL;
    }
    return $factory->initWithId($targetID);
  }

}
