<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Params\UserCreationParams;

class UserControllerFactory extends EntityControllerFactory {

  /**
   * @return UserController
   */
  public function initWithCurrentUser() {
    global $user;
    return parent::initWithId($user->uid);
  }

}
