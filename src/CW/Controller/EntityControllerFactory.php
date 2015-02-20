<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace CW\Controller;

use CW\Factory\Creator;
use CW\Model\ObjectHandler;
use CW\Util\LocalProcessIdentityMap;
use Exception;
use InvalidArgumentException;
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
   * @var LoggerInterface
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
   * @param string $entityType
   *  Entity type.
   * @param LoggerInterface $logger
   */
  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, ObjectHandler $objectLoader, $controllerClass, $entityType, LoggerInterface $logger) {
    $this->localProcessIdentityMap = $localProcessIdentityMap;

    if (!is_subclass_of($controllerClass, 'CW\Controller\AbstractEntityController')) {
      throw new InvalidArgumentException('Controller class is not subclass of CW\Controller\AbstractEntityController');
    }
    $this->controllerClass = $controllerClass;

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
  public function initWithId($entity_id, $cache_key = NULL) {
    $controller = NULL;

    $entity_id = (string) $entity_id;
    $isEntityIDMissing = strlen($entity_id) == 0;
    if ($isEntityIDMissing) {
      if ($cache_key === NULL) {
        throw new Exception('Missing entity id and cache key.');
      }
    }
    else {
      $cache_key = $entity_id;
    }

    $cacheKey = 'entity:' . $this->entityType . ':' . $cache_key;
    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $controller = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $controller = new $this->controllerClass($this->objectLoader, $this->logger, $this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $controller);
    }

    return $controller;
  }

  public function initWithEntity($entity) {
    list($id,,) = entity_extract_ids($this->entityType, $entity);

    if (strlen((string) $id) == 0) {
      $cache_key = spl_object_hash($entity);
    }
    else {
      $cache_key = $id;
    }

    $controller = $this->initWithId($cache_key);
    $controller->setDrupalEntity($entity);
    return $controller;
  }

  public function initNew(Creator $creator) {
    $entity = $creator->create();
    return $this->initWithEntity($entity);
  }

}
