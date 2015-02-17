<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Controller\AbstractEntityController;
use CW\Controller\UserController;
use CW\Params\UserCreationParams;

class UserCreator implements Creator {

  /**
   * @var \CW\Params\UserCreationParams
   */
  private $params;

  public function __construct(UserCreationParams $params) {
    $this->params = $params;
  }

  /**
   * @return AbstractEntityController
   */
  public function create() {
    $fields = array(
      'name' => $this->params->getUserName(),
      'mail' => $this->params->getEmail(),
      'pass' => $this->params->getPassword(),
      'status' => UserController::STATE_ACTIVE,
      'init' => $this->params->getEmail(),
      'roles' => $this->params->getRoles(),
    );

    $fields = array_merge($fields, $this->params->getExtraAttributes());

    $account = user_save(NULL, $fields);
    return $account;
  }

}
