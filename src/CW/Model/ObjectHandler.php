<?php
/**
 * @file
 *
 * Object loader.
 */

namespace CW\Model;

use EntityDrupalWrapper;

/**
 * Class ObjectHandler
 * @package CW\Model
 *
 * Low level object handler. Should be the system's handler, DB handler or maybe
 * some other alternative, such as cache, memcache, etc.
 */
abstract class ObjectHandler {

  /**
   * Load a single entity.
   *
   * @param string $entityType
   * @param int $entityId
   * @return object
   */
  abstract public function loadSingleEntity($entityType, $entityId);

  /**
   * Load multiple entities.
   *
   * @param string $entityType
   * @param array $entityIds
   * @return object[]
   */
  abstract public function loadMultipleEntity($entityType, array $entityIds);

  /**
   * Save entity data.
   *
   * @param string $entityType
   * @param object $entity
   * @return mixed
   */
  abstract public function save($entityType, $entity);

  /**
   * Delete entity.
   *
   * @param string $entityType
   * @param int $entityId
   * @return mixed
   */
  abstract public function delete($entityType, $entityId);

  /**
   * Load entity metadata wrapper.
   *
   * @param string $entityType
   * @param object $entity
   * @return EntityDrupalWrapper
   */
  abstract public function loadMetadata($entityType, $entity);

}