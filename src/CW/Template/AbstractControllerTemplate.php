<?php
/**
 * @file
 */

namespace CW\Template;

use CW\Controller\AbstractEntityController;

abstract class AbstractControllerTemplate {

  /**
   * @var AbstractEntityController
   */
  protected $controller;

  public function __construct(AbstractEntityController $controller) {
    $this->controller = $controller;
  }

}
