<?php
/**
 * @file
 *
 * Basic logical abstraction of a Drupal entity. Caches loaded entities and provide a facade to access Drupal
 * behaviors and the entity metadata wrapper items.
 *
 * Examples:
 *
 */

namespace CW\Model;

/**
 * Class CWToolEntityWrapper
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
  public $entityID;

  /**
   * The entity metadata wrapper object.
   * Use $this->getEntityMetadataWrapper() to access it.
   *
   * @var \EntityMetadataWrapper
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
   * @param string $entity_type
   *  Entity type.
   * @param string $entity_id
   *  Entity ID.
   */
  public function __construct($entity_type, $entity_id) {
    $this->entityType = $entity_type;
    $this->entityID = $entity_id;
  }

  /**
   * Get the entity metadata wrapper of the entity.
   *
   * @return \EntityMetadataWrapper
   *
   * @throws \Exception
   *  Entity metadata wrapper exception.
   */
  public function getEntityMetadataWrapper() {
    if (!isset($this->entityMetadataWrapper)) {
      $this->entityMetadataWrapper = entity_metadata_wrapper($this->entityType, $this->getDrupalEntityData());
    }

    return $this->entityMetadataWrapper;
  }

  /**
   * Get the Drupal object of the entity.
   *
   * @return mixed|object
   */
  public function getDrupalEntityData() {
    if (!isset($this->drupalEntityData)) {
      $this->drupalEntityData = entity_load_single($this->entityType, $this->entityID);
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
   * Implements self::__get().
   *
   * @throws \Exception
   *  Entity metadata wrapper exception.
   */
  public function __get($name) {
    // Proxy magic getter towards the entity metadata wrapper.
    return $this->getEntityMetadataWrapper()->{$name};
  }

  /**
   * Implements self::__set().
   *
   * @throws \Exception
   *  Entity metadata wrapper exception.
   */
  public function __set($name, $value) {
    // Proxy magic setter towards the entity metadata wrapper.
    $this->getEntityMetadataWrapper()->__set($name, $value);
    $this->setDirty();
  }

  public function save() {
    entity_save($this->entityType, $this->getDrupalEntityData());
    $this->setClean();
  }

  /**
   * @return boolean
   */
  public function isDirty() {
    return $this->isUpdated;
  }

  /**
   *
   */
  public function setDirty() {
    $this->isUpdated = TRUE;
  }

  /**
   *
   */
  public function setClean() {
    $this->isUpdated = FALSE;
  }

}
