<?php
/**
 * @file
 *
 * Entity model.
 */

namespace CW\Model;

use EntityMetadataWrapper;
use Exception;

/**
 * Class EntityModel
 * @package CW\Model
 *
 * Entity model. Provides access to the entity object and the entity metadata
 * wrapper instance.
 * Should contain all model related property accessor.
 */
class EntityModel implements IEntityModelConstructor {

  /**
   * Entity type.
   *
   * @var string
   */
  public $entityType;

  /**
   * Entity ID.
   *
   * @var int
   */
  public $entityId;

  /**
   * @var ObjectHandler
   */
  public $objectHandler;

  /**
   * The entity metadata wrapper object.
   * Use $this->getEntityMetadataWrapper() to access it.
   *
   * @var EntityMetadataWrapper
   */
  private $entityMetadataWrapper;

  /**
   * Drupal object.
   * Use $this->getDrupalObject() to access it.
   *
   * @var object
   */
  private $drupalEntityData;

  /**
   * Update flag.
   *
   * @var bool
   */
  private $isUpdated = FALSE;

  /**
   * Constructor.
   *
   * @param ObjectHandler $objectLoader
   * @param string $entity_type
   *  Entity type.
   * @param string $entity_id
   *  Entity ID.
   */
  public function __construct(ObjectHandler $objectLoader, $entity_type, $entity_id) {
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
  public function getEntityMetadataWrapper() {
    if (!isset($this->entityMetadataWrapper)) {
      $this->entityMetadataWrapper = $this->objectHandler->loadMetadata($this->entityType, $this->getEntityData());
    }

    return $this->entityMetadataWrapper;
  }

  /**
   * Get the Drupal object of the entity.
   *
   * @return mixed|object
   */
  public function getEntityData() {
    if (!isset($this->drupalEntityData)) {
      $this->drupalEntityData = $this->objectHandler->loadSingleEntity($this->entityType, $this->entityId);
    }

    return $this->drupalEntityData;
  }

  /**
   * Sets the Drupal object.
   *
   * @param object $drupalEntityData
   *  Drupal object.
   */
  public function setDrupalEntityData($drupalEntityData) {
    $this->drupalEntityData = $drupalEntityData;
  }

  /**
   * Save data to database.
   */
  public function save() {
    $this->objectHandler->save($this->entityType, $this->getEntityData());
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
    return '[' . get_class($this) . ', ' . $this->entityType . ', ' . $this->entityId . ']@' . spl_object_hash($this);
  }

}
