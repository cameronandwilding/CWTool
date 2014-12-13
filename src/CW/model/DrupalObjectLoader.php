<?php
/**
 * @file
 *
 * Drupal object loader.
 */

namespace CW\Model;

/**
 * Class DrupalObjectLoader
 * @package CW\Model
 *
 * Drupal API object loader.
 */
class DrupalObjectLoader extends ObjectLoader {

  /**
   * {@inheritdoc}
   */
  public function loadSingleEntity($entityType, $entityId) {
    return entity_load_single($entityType, $entityId);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleEntity($entityType, array $entityIds) {
    return entity_load($entityType, $entityIds);
  }

  /**
   * {@inheritdoc}
   */
  public function save($entityType, $entity) {
    return entity_save($entityType, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMetadata($entityType, $entity) {
    return entity_metadata_wrapper($entityType, $entity);
  }

}
