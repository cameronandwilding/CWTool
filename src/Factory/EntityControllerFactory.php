<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace Drupal\cw_tool\Factory;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\cw_tool\Controller\AbstractEntityController;

/**
 * Class EntityControllerFactory
 * @package Drupal\cw_tool\Factory
 *
 * The purpose of this class is to create entity controllers.
 */
class EntityControllerFactory {

  const MASTER_CONTROLLER_CLASS = 'Drupal\cw_tool\Controller\AbstractEntityController';

  /**
   * @var string
   */
  private $entityType;

  /**
   * @var string
   */
  private $controllerClass;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * EntityControllerFactory constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Drupal entity manager.
   * @param string $entityType
   *   Entity type.
   * @param string $controllerClass
   *   Actual entity controller class.
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(EntityManagerInterface $entityManager, $entityType, $controllerClass) {
    $this->entityType = $entityType;

    if (!is_subclass_of($controllerClass, self::MASTER_CONTROLLER_CLASS)) {
      throw new \InvalidArgumentException('Controller class has to be subclass of: ' . self::MASTER_CONTROLLER_CLASS);
    }

    $this->controllerClass = $controllerClass;
    $this->entityManager = $entityManager;
  }

  /**
   * Factory method to provide the controller by id.
   *
   * @param int|null $id
   *   ID of the entity.
   *
   * @return AbstractEntityController
   *   Instance of the controller.
   */
  public function initWithID($id) {
    return new $this->controllerClass($this->entityManager, $this->entityType, $id);
  }

  /**
   * Factory method to provide the controller by id.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return AbstractEntityController
   *   Instance of the controller.
   */
  public function initWithEntity(EntityInterface $entity) {
    $controller = $this->initWithID($entity->id());
    $controller->setEntity($entity);
    return $controller;
  }

}
