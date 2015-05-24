<?php
/**
 * @file
 *
 * Abstract controller container.
 */

namespace CW\Controller;

/**
 * Class AbstractControllerTemplate
 * @package CW\Controller
 *
 * Controller container is a class that has an entity controller as the main
 * object.
 *
 * @todo consider making it a trait
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

  /**
   * @return \CW\Controller\AbstractEntityController
   */
  public function getController() {
    return $this->controller;
  }

  /**
   * Factory helper.
   *
   * @param \CW\Controller\AbstractEntityController $controller
   * @return static
   */
  public static function factory(AbstractEntityController $controller) {
    return new static($controller);
  }

}
