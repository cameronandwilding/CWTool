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
 * Class NodeControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create
 */
class NodeControllerFactory {

  private $defaultEntityControllerClass;

  private $controllerClassMap = array();

  /**
   * @var \CW\Util\EntityLocalProcessIdentityMap
   */
  private $entityModelLoader;

  public function __construct(EntityLocalProcessIdentityMap $entityModelLoader, $defaultEntityControllerClass) {
    if (!is_subclass_of($defaultEntityControllerClass, 'CW\Controller\AbstractEntityController')) {
      throw new \InvalidArgumentException('Class is not subclass of AbstractEntityController: ' . $defaultEntityControllerClass);
    }

    $this->defaultEntityControllerClass = $defaultEntityControllerClass;

    $this->entityModelLoader = $entityModelLoader;

    $args = func_get_args();
    array_shift($args); // Remove entity model loader.
    array_shift($args); // Remove base class.
    if (count($args) > 0) {
      while (count($args)) {
        $contentTypeName = array_shift($args);
        $controllerClass = array_shift($args);
        $this->controllerClassMap[$contentTypeName] = $controllerClass;
      }
    }
  }

  public function get($nid) {
    /** @var EntityModel $node */
    $node = $this->entityModelLoader->getFromEntityID('node', $nid);

    $nodeType = $node->getDrupalEntityData()->type;
    if (array_key_exists($nodeType, $this->controllerClassMap)) {
      $controllerClass = $this->controllerClassMap[$nodeType];
    }
    else {
      $controllerClass = $this->defaultEntityControllerClass;
    }
    $controller = new $controllerClass($node);

    return $controller;
  }

}
