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
  private $objectHandler;

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
    $this->objectHandler = $objectLoader;
    $this->logger = $logger;
  }

  /**
   * Factory method.
   *
   * @param mixed $entity_id
   * @param null $cacheKey
   * @return \CW\Controller\AbstractEntityController
   * @throws \CW\Exception\IdentityMapException
   * @throws \Exception
   */
  public function initWithId($entity_id, $cacheKey = NULL) {
    $controller = NULL;

    // Sometimes ID is unavailable, so cacheKey can be provided explicitly.
    // Otherwise it generates an automatic cache key.
    if ($cacheKey === NULL) {
      $isEntityIDMissing = strlen($entity_id) === 0;
      if ($isEntityIDMissing) {
        throw new InvalidArgumentException('Missing entity id and cache key.');
      }
      $cacheKey = 'entity:' . $this->entityType . ':' . $entity_id;
    }

    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $controller = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $controller = new $this->controllerClass($this->objectHandler, $this->logger, $this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $controller);
    }

    return $controller;
  }

  public function initWithEntity($entity) {
    list($id,,) = entity_extract_ids($this->entityType, $entity);

    // Entity can be new - without and ID - cache key is important so we use the object hash PHP provides.
    if (strlen($id) == 0) {
      $cache_key = spl_object_hash($entity);
    }
    else {
      $cache_key = NULL;
    }

    $controller = $this->initWithId($id, $cache_key);
    $controller->setEntity($entity);
    return $controller;
  }

  public function initNew(Creator $creator) {
    $entity = $creator->create();
    return $this->initWithEntity($entity);
  }

}
