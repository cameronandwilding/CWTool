<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace CW\Controller;

use CW\Model\EntityModel;
use CW\Model\ObjectHandler;
use CW\Params\EntityCreationParams;
use CW\Util\LocalProcessIdentityMap;
use Exception;
use Psr\Log\LoggerInterface;

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
   * @var ObjectHandler
   */
  private $objectLoader;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $localProcessIdentityMap
   *  Identity map cache.
   * @param ObjectHandler $objectLoader
   *  Low level data loader.
   * @param string $controllerClass
   *  Actual entity controller class.
   * @param string $modelClass
   *  Actual entity model class.
   * @param string $entityType
   *  Entity type.
   */
  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, ObjectHandler $objectLoader, $controllerClass, $modelClass, $entityType, LoggerInterface $logger) {
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
    $this->logger = $logger;
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

    $controller = new $this->controllerClass($entityModel, $this->logger);

    return $controller;
  }

  public function initWithEntity($entity) {
    list($id,,) = entity_extract_ids($this->entityType, $entity);
    $controller = $this->initWithId($id);
    $controller->getEntityModel()->setDrupalEntityData($entity);
    return $controller;
  }

  public function initNew(EntityCreationParams $params) {
    $creator = array($this->controllerClass, 'createRaw');
    if (!is_callable($creator)) {
      throw new Exception('Controller class does not have a createRaw method');
    }

    $entity = call_user_func($creator, $params);
    return $this->initWithEntity($entity);
  }

}
