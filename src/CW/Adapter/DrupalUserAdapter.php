<?php
/**
 * @file
 *
 * Drupal functionality for user operations.
 */

namespace CW\Adapter;

use CW\Structure\Singleton;
use Psr\Log\LoggerInterface;

/**
 * Class DrupalUserAdapter
 *
 * Service to do common session related user operations.
 *
 * Example 1:
 * Log a user account in:
 *
 * @code
 * $userAdapter = DrupalUserAdapter::getInstance();
 * $userAdapter->login(user_load(123), cw_tool_get_container()[CWTOOL_SERVICE_LOGGER]);
 * @endcode
 *
 * @package CW\Adapter
 */
class DrupalUserAdapter {

  use Singleton;

  /**
   * @return string
   */
  public function getGlobalUserObject() {
    global $user;
    return $user;
  }

  /**
   * @param object $account
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function login($account, LoggerInterface $logger) {
    global $user;

    $user = $account;

    $logger->info(__METHOD__ . ' session opened for {name}.', array('name' => $user->name));

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
