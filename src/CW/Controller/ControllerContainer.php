<?php
/**
 * @file
 *
 * Abstract controller template.
 */

namespace CW\Controller;

/**
 * Class AbstractControllerTemplate
 * @package CW\Controller
 *
 * Abstract controller templates are used to group theming methods related to
 * a controller.
 */
abstract class ControllerContainer implements ControllerAware {

  /**
   * @var AbstractEntityController
   */
  private $controller;

  /**
   * @param \CW\Controller\AbstractEntityController $controller
   */
  public function __construct(AbstractEntityController $controller) {
    $this->controller = $controller;
  }

  public function getController() {
    return $this->controller;
  }

}
