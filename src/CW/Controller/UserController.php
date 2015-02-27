<?php
/**
 * @file
 *
 * User controller.
 */

namespace CW\Controller;

/**
 * Class UserController
 * @package CW\Controller
 */
class UserController extends AbstractEntityController {

  // Flag for current user evaluation.
  // Used for marking the current user, especially when the execution is delayed
  // or scheduled and the current user of the future session is intended for use.
  const USER_CURRENT = -1;

  const UID_ANONYMOUS = 0;
  const UID_ADMIN = 1;

  const STATE_ACTIVE = 1;
  const STATE_BLOCKED = 0;

  const ROLE_AUTHENTICATED_USER = 'authenticated user';

  /**
   * @return bool
   */
  public function isCurrent() {
    return self::currentUID() == $this->getEntityId();
  }

  /**
   * @return mixed
   */
  public static function currentUID() {
    global $user;
    return $user->uid;
  }

  /**
   * @return bool
   */
  public function isAdmin() {
    return $this->getEntityId() == self::UID_ADMIN;
  }

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return 'user';
  }

  /**
   * Drupal login.
   */
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
