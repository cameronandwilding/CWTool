<?php
/**
 * @file
 *
 * Drupal object handler.
 */

namespace CW\Model;

/**
 * Class DrupalObjectHandler
 * @package CW\Model
 *
 * Drupal API object handler.
 */
class DrupalObjectHandler extends ObjectHandler {

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

  /**
   * {@inheritdoc}
   */
  public function delete($entityType, $entityId) {
    return entity_delete($entityType, $entityId);
  }

}
