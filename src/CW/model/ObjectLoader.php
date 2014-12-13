<?php
/**
 * @file
 *
 * Object loader.
 */

namespace CW\Model;

use EntityDrupalWrapper;

/**
 * Class ObjectLoader
 * @package CW\Model
 *
 * Low level object loader. Should be the system's loader, DB loader or maybe
 * some other alternative, such as cache, memcache, etc.
 */
abstract class ObjectLoader {

  /**
   * Load a single entity.
   *
   * @param string $entityType
   * @param mixed $entityId
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
   * Load entity metadata wrapper.
   *
   * @param string $entityType
   * @param object $entity
   * @return EntityDrupalWrapper
   */
  abstract public function loadMetadata($entityType, $entity);

}
