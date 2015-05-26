<?php
/**
 * @file
 *
 * Validable object.
 */

namespace CW\Validator;

/**
 * Interface Validable
 * @package CW\Validator
 */
interface Validable {

  /**
   * @return bool
   */
  public function isValid();

}