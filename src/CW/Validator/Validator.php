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
   * @return bool
   * @todo might not need boolean return
   */
  public function validate();

}
