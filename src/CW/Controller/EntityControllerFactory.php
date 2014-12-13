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

  public function __construct(EntityLocalProcessIdentityMap $entityModelLoader, $controllerClass) {
    $this->entityModelLoader = $entityModelLoader;
    // @todo add check to base class
    $this->controllerClass = $controllerClass;
  }

  public function initWithTypeAndId($entity_type, $entity_id) {
    /** @var EntityModel $entityModel */
    $entityModel = $this->entityModelLoader->getFromEntityID($entity_type, $entity_id);
    $controller = new $this->controllerClass($entityModel);
    return $controller;
  }

}
