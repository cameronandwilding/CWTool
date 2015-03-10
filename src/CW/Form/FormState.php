<?php
/**
 * @file
 */

namespace CW\Form;


class FormState {

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