<?php
/**
 * @file
 *
 * Validator.
 */

namespace CW\Validator;

/**
 * Interface Validator
 *
 * Used for validation.
 */
interface Validator {

  /**
   * Validate a state.
   * Preferred error handling is changing global state (by setting form error)
   * or firing an exception.
   */
  public function validate();

}
