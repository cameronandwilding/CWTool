<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Params\UserCreationParams;

class UserController extends AbstractEntityController {

  const ADMIN_UID = 1;

  public function isCurrent() {
    global $user;
    return $user->uid == $this->getEntityModel()->entityId;
  }

  public function isAdmin() {
    return $this->getEntityModel()->entityId == self::ADMIN_UID;
  }

  public static function createRaw(UserCreationParams $params) {
    $fields = array(
      'name' => $params->getUserName(),
      'mail' => $params->getEmail(),
      'pass' => $params->getPassword(),
      'status' => 1,
      'init' => $params->getEmail(),
      'roles' => $params->getRoles(),
    );

    $fields = array_merge($fields, $params->getExtraAttributes());

    $account = user_save(NULL, $fields);
    return $account;
  }

}
