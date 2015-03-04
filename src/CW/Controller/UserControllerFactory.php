<?php
/**
 * @file
 *
 * User controller factory.
 */

namespace CW\Controller;

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
