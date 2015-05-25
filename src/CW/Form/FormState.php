<?php
/**
 * @file
 *
 * Form state.
 */

namespace CW\Form;

use itarato\VarCheck\VC;

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

  /**
   * Constructor.
   *
   * @param array $formState
   */
  public function __construct(&$formState) {
    $this->formState = $formState;
  }

  /**
   * Get form value.
   *
   * @param string $key
   * @return mixed
   */
  public function val($key) {
    return isset($this->formState[self::VALUES_KEY][$key]) ? $this->formState[self::VALUES_KEY][$key] : NULL;
  }

  /**
   * Returns the VarCheck wrapped values for easy access.
   *
   * @return \itarato\VarCheck\VC
   */
  public function getWrappedVales() {
    return VC::make($this->formState[self::VALUES_KEY]);
  }

}
