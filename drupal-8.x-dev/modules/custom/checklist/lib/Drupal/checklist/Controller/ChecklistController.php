<?php

/**
 * @file
 * Contains \Drupal\checklist\Controller\ChecklistController.
 */


namespace Drupal\checklist\Controller;
use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityListControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Controller\ControllerBase;




class ChecklistController extends ControllerBase {

  public function getFormID() {
    return 'checklist_settings';
  }


  public function checklistRender() {

  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Example');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }
  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
