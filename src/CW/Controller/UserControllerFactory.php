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

  public function initNew(UserCreationParams $params) {
    $creator = array($this->controllerClass, 'createRaw');
    if (!is_callable($creator)) {
      throw new Exception('');
    }

    $account = call_user_func($creator, $params);
    /** @var UserController $userController */
    $userController = $this->initWithId($account->uid);
    $userController->getEntityModel()->setDrupalEntityData($account);
    return $userController;
  }

}
