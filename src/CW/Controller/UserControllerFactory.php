<?php
/**
 * @file
 */

namespace CW\Controller;

class UserControllerFactory extends EntityControllerFactory {

  /**
   * @return UserController
   */
  public function initWithCurrentUser() {
    global $user;
    return parent::initWithId($user->uid);
  }

  public function initNew() {

  }

}
