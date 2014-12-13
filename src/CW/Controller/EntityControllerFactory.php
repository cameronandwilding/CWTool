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
  }

  public function registerEntityBundleClass($entity_type, $bundle, $class) {
    $this->controllerClassMap[$entity_type][$bundle] = $class;
  }

  public function get($entity_type, $entity_id) {
    /** @var EntityModel $entityModel */
    $entityModel = $this->entityModelLoader->getFromEntityID($entity_type, $entity_id);

    list(,, $bundle) = entity_extract_ids($entity_type, $entityModel->getDrupalEntityData());
    if (!empty($this->controllerClassMap[$entity_type][$bundle])) {
      $controllerClass = $this->controllerClassMap[$entity_type][$bundle];
    }
    else {
      $controllerClass = $this->defaultEntityControllerClass;
    }
    $controller = new $controllerClass($entityModel);

    return $controller;
  }

}
