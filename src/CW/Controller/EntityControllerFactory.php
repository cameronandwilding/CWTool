<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace CW\Controller;

use CW\Exception\IdentityMapException;
use CW\Model\EntityModel;
use CW\Model\ObjectLoader;
use CW\Util\LocalProcessIdentityMap;

/**
 * Class EntityControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create entity controllers.
 */
class EntityControllerFactory {

  /**
   * Identity map cache.
   *
   * @var LocalProcessIdentityMap
   */
  private $localProcessIdentityMap;

  /**
   * Actual entity controller class to instantiate.
   *
   * @var string
   */
  protected $controllerClass;

  /**
   * Corresponding entity model class.
   *
   * @var string
   */
  protected $modelClass;

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Object loader that takes care of low level data loading.
   *
   * @var ObjectLoader
   */
  private $objectLoader;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $localProcessIdentityMap
   *  Identity map cache.
   * @param ObjectLoader $objectLoader
   *  Low level data loader.
   * @param string $controllerClass
   *  Actual entity controller class.
   * @param string $modelClass
   *  Actual entity model class.
   * @param string $entityType
   *  Entity type.
   */
  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, ObjectLoader $objectLoader, $controllerClass, $modelClass, $entityType) {
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

    $this->objectLoader = $objectLoader;
  }

  /**
   * Factory method.
   *
   * @param mixed $entity_id
   * @return AbstractEntityController
   */
  public function initWithId($entity_id) {
    /** @var EntityModel $entityModel */
    $entityModel = NULL;

    $cacheKey = 'entity:' . $this->entityType . ':' . $entity_id;
    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $entityModel = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $entityModel = new $this->modelClass($this->objectLoader, $this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $entityModel);
    }

    $controller = new $this->controllerClass($entityModel);

    return $controller;
  }

}
