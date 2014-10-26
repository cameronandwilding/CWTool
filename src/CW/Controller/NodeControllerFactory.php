<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Model\EntityModel;

class NodeControllerFactory {

  private $baseClass;

  private $controllerClassMap = array();

  public function __construct($baseClass) {
    if (!is_subclass_of($baseClass, 'CW\Controller\AbstractEntityController')) {
      throw new \InvalidArgumentException('Class is not subclass of AbstractEntityController: ' . $baseClass);
    }

    $this->baseClass = $baseClass;

    $args = func_get_args();
    array_shift($args);
    if (count($args) > 0) {
      while (count($args)) {
        $contentTypeName = array_shift($args);
        $controllerClass = array_shift($args);
        $this->controllerClassMap[$contentTypeName] = $controllerClass;
      }
    }
  }

  public function get($nid) {
    $container = cw_tool_get_container();
    $loader = $container->get('entity-loader');
    /** @var EntityModel $node */
    $node = $loader->getFromEntityID('node', $nid);

    $nodeType = $node->getDrupalEntityData()->type;
    if (array_key_exists($nodeType, $this->controllerClassMap)) {
      $controllerClass = $this->controllerClassMap[$nodeType];
    }
    else {
      $controllerClass = $this->baseClass;
    }
    $controller = new $controllerClass($node);

    return $controller;
  }

}
