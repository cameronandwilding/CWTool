<?php
/**
 * @file
 */

namespace Drupal\cw_tool\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;

class AbstractEntityController {

  /**
   * @var EntityInterface
   */
  private $entity;

  /**
   * @var EntityStorageInterface
   */
  private $storage;

  /**
   * @var string
   */
  private $entityID;

  /**
   * @var string
   */
  private $entityType;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager, $entityType, $entityID) {
    $this->entityID = $entityID;
    $this->entityType = $entityType;
    $this->entityManager = $entityManager;
  }

  private function getEntityStorage() {
    if (empty($this->storage)) {
      $this->storage = $this->entityManager->getStorage($this->entityType);
    }
    return $this->storage;
  }

  /**
   * @return EntityInterface
   */
  public function getEntity() {
    if (empty($this->entity)) {
      $this->entity = $this->getEntityStorage()->load($this->entityID);
    }
    return $this->entity;
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @return string
   */
  public function getEntityID() {
    return $this->entityID;
  }

}
