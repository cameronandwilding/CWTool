<?php
/**
 * @file
 *
 * User controller factory.
 */

namespace CW\Factory;

use CW\Controller\UserController;

/**
 * Class UserControllerFactory
 * @package CW\Controller
 */
class UserControllerFactory extends EntityControllerFactory {

  /**
   * @return UserController
   */
  public function initWithCurrentUser() {
    return $this->initWithId(UserController::currentUID());
  }

}
