<?php
/**
 * @file
 *
 * User is logged in validator.
 */

namespace CW\Validator;

use CW\Structure\Singleton;

/**
 * Class UserLoggedInValidator
 * @package Debates\Core\Validator
 */
class UserLoggedInValidator implements Validable {

  use Singleton;

  /**
   * @return bool
   */
  public function isValid() {
    return user_is_logged_in();
  }

}
