<?php
/**
 * @file
 *
 * Abstract controller template.
 */

namespace CW\Template;

use CW\Controller\AbstractEntityController;

/**
 * Class AbstractControllerTemplate
 * @package CW\Template
 *
 * Abstract controller templates are used to group theming methods related to
 * a controller.
 */
abstract class AbstractControllerTemplate {

  /**
   * @var AbstractEntityController
   */
  protected $controller;

  /**
   * @param \CW\Controller\AbstractEntityController $controller
   */
  public function __construct(AbstractEntityController $controller) {
    $this->controller = $controller;
  }

}
