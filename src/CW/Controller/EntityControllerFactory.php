<?php
/**
 * @file
 *
 * Node controller factory.
 */

namespace CW\Controller;

use CW\Model\EntityModel;
use CW\Util\EntityLocalProcessIdentityMap;

/**
 * Class EntityControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create entity controllers.
 */
class EntityControllerFactory {

  /**
   * @var \CW\Util\EntityLocalProcessIdentityMap
   */
  private $entityModelLoader;

  protected $controllerClass;

  protected $entityType;

  public function __construct(EntityLocalProcessIdentityMap $entityModelLoader, $controllerClass, $entityType) {
    $this->entityModelLoader = $entityModelLoader;
    // @todo add check to base class
    $this->controllerClass = $controllerClass;
    $this->entityType = $entityType;
  }

  public function initWithId($entity_id) {
    /** @var EntityModel $entityModel */
    $entityModel = $this->entityModelLoader->getFromEntityID($this->entityType, $entity_id);
    $controller = new $this->controllerClass($entityModel);
    return $controller;
  }

}
