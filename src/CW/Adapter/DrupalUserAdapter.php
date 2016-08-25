<?php
/**
 * @file
 *
 * Drupal functionality for user operations.
 */

namespace CW\Adapter;

use Psr\Log\LoggerInterface;

/**
 * Class DrupalUserAdapter
 * @package CW\Adapter
 */
class DrupalUserAdapter {

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
