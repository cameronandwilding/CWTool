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

  // Basic user IDs.
  const UID_ANONYMOUS = 0;
  const UID_ADMIN = 1;

  // User states.
  const STATE_ACTIVE = 1;
  const STATE_BLOCKED = 0;

  // Role name.
  const ROLE_AUTHENTICATED_USER = 'authenticated user';

  // Entity type.
  const TYPE_USER = 'user';

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
    return self::TYPE_USER;
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

  /**
   * @param string $roleName
   * @return bool
   */
  public function hasRole($roleName) {
    return isset($this->entity()->roles) && in_array($roleName, $this->entity()->roles);
  }

  /**
   * User entity exist in GLOBAL user object - but that's incomplete sometimes
   * as it does not contain fields or other properties.
   * It is "incomplete" when not loaded through the full entity load process.
   *
   * @return bool
   */
  protected function isUserEntityIncomplete() {
    $entity = parent::entity();
    return isset($entity->sid);
  }

  /**
   * {@inheritdoc}
   */
  public function entity($forceReload = self::RELOAD_IGNORE) {
    // Flag to make sure it's reloaded only once at max.
    static $realEntityLoadedFlag = FALSE;

    if (!$realEntityLoadedFlag && $this->isUserEntityIncomplete()) {
      $realEntityLoadedFlag = TRUE;
      return parent::entity(self::RELOAD_FORCE);
    }

    return parent::entity($forceReload);
  }

}
