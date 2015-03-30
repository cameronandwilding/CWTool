<?php
/**
 * @file
 *
 * Form validator.
 */

namespace CW\Validator;

use CW\Form\FormState;

/**
 * Class FormValidator
 * @package CW\Validator
 *
 * Validator for forms.
 */
abstract class FormValidator implements Validator {

  /**
   * Form state.
   *
   * @var \CW\Form\FormState
   */
  protected $formState;

  /**
   * @param FormState $formState
   */
  public function __construct(FormState $formState) {
    $this->formState = $formState;
  }

}
