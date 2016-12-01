<?php

namespace Drupal\cw_tool\Factory;

use Drupal\cw_tool\Params\EntityCreationParams;
use Drupal\Core\Entity\EntityInterface;

/**
 * Creation factory for Drupal entities.
 */
class EntityCreator {

  /**
   * @var string
   */
  private $entityClass;

  /**
   * EntityCreator constructor.
   *
   * @param string $entityClass
   *   Class that implements EntityInterface.
   */
  public function __construct($entityClass) {
    $this->entityClass = $entityClass;
  }

  /**
   * Create the entity.
   *
   * @param \Drupal\cw_tool\Params\EntityCreationParams $params
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function create(EntityCreationParams $params) {
    /** @var EntityInterface $entity */
    $entity = call_user_func([$this->entityClass, 'create'], $params->getValues());
    $entity->save();
    return $entity;
  }

}
