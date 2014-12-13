<?php
/**
 * @file
 */

namespace CW\Model;

class DrupalObjectLoader extends ObjectLoader {

  public function loadSingleEntity($entityType, $entityId) {
    return entity_load_single($entityType, $entityId);
  }

  public function loadMultipleEntity($entityType, array $entityIds) {
    return entity_load($entityType, $entityIds);
  }

  public function save($entityType, $entity) {
    return entity_save($entityType, $entity);
  }

}
