<?php
/**
 * @file
 */

namespace CW\Controller;

class NodeController extends AbstractEntityController {

  public function getAuthor() {
    $uid = $this->entityModel->getDrupalEntityData()->uid;
    return $uid;
  }

}