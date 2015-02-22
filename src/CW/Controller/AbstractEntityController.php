<?php
/**
 * @file
 *
 * Entity controller abstraction.
 */

namespace CW\Controller;

use CW\Model\ObjectHandler;
use EntityMetadataWrapper;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractEntityController
 * @package CW\Controller
 *
 * Abstraction for entity controller. Contains the model and should be extended
 * for content specific behaviors.
 */
abstract class AbstractEntityController {

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

  public static function getClassEntityType() {
    throw new Exception('Undefined entity type');
  }

  public static function getClassEntityBundle() {
    throw new Exception('Undefined entity bundle');
  }

}
