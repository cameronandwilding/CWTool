<?php
/**
 * @file
 *
 * Form state.
 */

namespace CW\Form;

/**
 * Class FormState
 * @package CW\Form
 *
 * Form state is keeping the $form_state variable populated by Drupal and
 * FormAPI and ads behavior.
 */
class FormState {

  // Value key.
  const VALUES_KEY = 'values';

  /**
   * @var array
   */
  protected $formState;

  public function __construct(&$formState) {
    $this->formState = $formState;
  }

  public function val($key) {
    return isset($this->formState[self::VALUES_KEY][$key]) ? $this->formState[self::VALUES_KEY][$key] : NULL;
  }

}