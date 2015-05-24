<?php
/**
 * @file
 *
 * Node form validator.
 */

namespace CW\Validator;

use CW\Form\NodeFormState;

/**
 * Class NodeFormValidator
 * @package CW\Validator
 *
 * Validates node edit form results.
 */
abstract class NodeFormValidator implements Validator {

  /**
   * Form state.
   *
   * @var \CW\Form\NodeFormState
   */
  protected $formState;

  /**
   * @param \CW\Form\NodeFormState $formState
   */
  public function __construct(NodeFormState $formState) {
    $this->formState = $formState;
  }

}
