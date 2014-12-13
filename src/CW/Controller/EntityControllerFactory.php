<?php
/**
 * @file
 *
 * Node controller factory.
 */

namespace CW\Controller;

use CW\Model\EntityModel;
use CW\Util\LocalProcessIdentityMap;

/**
 * Class EntityControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create entity controllers.
 */
class EntityControllerFactory {

  /**
   * @var LocalProcessIdentityMap
   */
  private $localProcessIdentityMap;

  protected $controllerClass;

  protected $modelClass;

  protected $entityType;

  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, $controllerClass, $modelClass, $entityType) {
    $this->localProcessIdentityMap = $localProcessIdentityMap;

    if (!is_subclass_of($controllerClass, 'CW\Controller\AbstractEntityController')) {
      throw new \InvalidArgumentException('Controller class is not subclass of CW\Controller\AbstractEntityController');
    }
    $this->controllerClass = $controllerClass;

    if (!is_subclass_of($modelClass, 'CW\Model\IEntityModelConstructor')) {
      throw new \InvalidArgumentException('Model class does not implement CW\Model\IEntityModelConstructor');
    }
    $this->modelClass = $modelClass;

    $this->entityType = $entityType;
  }

  public function initWithId($entity_id) {
    /** @var EntityModel $entityModel */
    $entityModel = NULL;

    $cacheKey = 'entity:' . $this->entityType . ':' . $entity_id;
    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $entityModel = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $entityModel = new $this->modelClass($this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $entityModel);
    }

    $controller = new $this->controllerClass($entityModel);

    return $controller;
  }

}
