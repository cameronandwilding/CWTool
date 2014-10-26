<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Model\EntityModel;

abstract class AbstractEntityController {

  /**
   * @var \CW\Model\EntityModel
   */
  protected $entityModel;

  public function __construct(EntityModel $entityModel) {
    $this->entityModel = $entityModel;
  }

}