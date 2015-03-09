<?php
/**
 * @file
 *
 * Controller aware.
 */

namespace CW\Controller;

/**
 * Interface ControllerAware
 * @package CW\Controller
 *
 * Describes an object that has an entityController.
 */
interface ControllerAware {

  /**
   * @return AbstractEntityController
   */
  public function getController();

}
