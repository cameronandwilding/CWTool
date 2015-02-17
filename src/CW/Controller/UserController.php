<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Params\UserCreationParams;

/**
 * Class UserController
 * @package CW\Controller
 */
class UserController extends AbstractEntityController {

  const USER_CURRENT = -1;
  const UID_ANONYMOUS = 0;
  const UID_ADMIN = 1;

  const STATE_ACTIVE = 1;
  const STATE_BLOCKED = 0;

  const ROLE_AUTHENTICATED_USER = 'authenticated user';

  public function isCurrent() {
    global $user;
    return $user->uid == $this->entityId;
  }

  public function isAdmin() {
    return $this->entityId == self::UID_ADMIN;
  }

  public static function getClassEntityType() {
    return 'user';
  }

}
