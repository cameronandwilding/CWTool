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
   * @param array $form_state
   */
  public function __construct($form_state) {
    $this->formState = new FormState($form_state);
  }

}
