<?php
/**
 * @file
 */

namespace CW\Util;

class EntityLocalProcessIdentityMap extends LocalProcessIdentityMap {

  private $entityClass;

  public function __construct($entityClass) {
    $implementations = class_implements($entityClass);
    if (!isset($implementations['CW\Model\IEntityModelConstructor'])) {
      throw new \InvalidArgumentException('Entity class is not instance of IEntityModelConstructor: ' . $entityClass);
    }

    $this->entityClass = $entityClass;
  }

  protected static function createKey($id, $entity_type) {
    return "entity:$entity_type:$id";
  }

  /**
   * @param $entity_type
   * @param $entity
   * @return \CW\Model\EntityModel
   * @throws \CW\Exception\IdentityMapException
   * @throws \EntityMalformedException
   */
  public function getFromEntity($entity_type, $entity) {
    list($entity_id) = entity_extract_ids($entity_type, $entity);
    return $this->getFromEntityID($entity_type, $entity_id);
  }

  public function getFromEntityID($entity_type, $entity_id) {
    $key = self::createKey($entity_id, $entity_type);
    if ($this->keyExist($key)) {
      return $this->get($key);
    }

    $entity = new $this->entityClass($entity_type, $entity_id);
    $this->add($key, $entity);
    return $entity;
  }

}
