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
   * @var ObjectLoader
   */
  public $objectLoader;

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
   * @param ObjectLoader $objectLoader
   * @param string $entity_type
   *  Entity type.
   * @param string $entity_id
   *  Entity ID.
   */
  public function __construct(ObjectLoader $objectLoader, $entity_type, $entity_id) {
    $this->entityType = $entity_type;
    $this->entityId = $entity_id;
    $this->objectLoader = $objectLoader;
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
      $this->entityMetadataWrapper = $this->objectLoader->loadMetadata($this->entityType, $this->getEntityData());
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
      $this->drupalEntityData = $this->objectLoader->loadSingleEntity($this->entityType, $this->entityId);
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
    $this->objectLoader->save($this->entityType, $this->getEntityData());
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

}