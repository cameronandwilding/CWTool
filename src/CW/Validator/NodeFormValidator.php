<?php
/**
 * @file
 */

namespace CW\Validator;

use CW\Form\NodeFormState;

abstract class NodeFormValidator extends FormValidator {

  /**
   * Form state.
   *
   * @var \CW\Form\NodeFormState
   */
  protected $formState;

  public function __construct($form_state) {
    $this->formState = new NodeFormState($form_state);
  }

}
