<?php
/**
 * @file
 */

namespace CW\Controller;

class UserController extends NodeController {

  const ADMIN_UID = 1;

  public function isCurrent() {
    global $user;
    return $user->uid == $this->getEntityModel()->entityId;
  }

  public function isAdmin() {
    return $this->getEntityModel()->entityId == self::ADMIN_UID;
  }
  
}
