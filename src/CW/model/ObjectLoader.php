<?php
/**
 * @file
 */

namespace CW\Model;


abstract class ObjectLoader {

  abstract public function loadSingleEntity($entityType, $entityId);

  abstract public function loadMultipleEntity($entityType, array $entityIds);

  abstract public function save($entityType, $entity);

}
