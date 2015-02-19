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

  public function login() {
    global $user;
    // Override global user.
    $user = $this->entity();

    $this->logger->info(__METHOD__ . ' session opened for {name}.', array('name' => $user->name));

    // Update the user table timestamp noting user has logged in.
    // This is also used to invalidate one-time login links.
    $user->login = REQUEST_TIME;
    db_update('users')
      ->fields(array('login' => $user->login))
      ->condition('uid', $user->uid)
      ->execute();

    // Regenerate the session ID to prevent against session fixation attacks.
    // This is called before hook_user in case one of those functions fails
    // or incorrectly does a redirect which would leave the old session in place.
    drupal_session_regenerate();
  }

}
