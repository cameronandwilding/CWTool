<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace Drupal\cw_tool\Factory;

use Drupal\Core\Entity\EntityManagerInterface;

class EntityControllerFactory {

  const MASTER_CONTROLLER_CLASS = 'Drupal\cw_tool\Controller\AbstractEntityController';

  private $entityType;

  private $controllerClass;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager, $entityType, $controllerClass) {
    $this->entityType = $entityType;

    if (!is_subclass_of($controllerClass, self::MASTER_CONTROLLER_CLASS)) {
      throw new \InvalidArgumentException('Controller class has to be subclass of: ' . self::MASTER_CONTROLLER_CLASS);
    }

    $this->controllerClass = $controllerClass;
    $this->entityManager = $entityManager;
  }

  public function initWithID($id) {
    return new $this->controllerClass($this->entityManager, $this->entityType, $id);
  }

}
