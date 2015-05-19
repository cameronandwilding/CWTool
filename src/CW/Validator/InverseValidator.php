<?php
/**
 * @file
 */

namespace CW\Validator;

class InverseValidator implements Validable {

  /**
   * @var \CW\Validator\Validable
   */
  private $validable;

  public function __construct(Validable $validable) {
    $this->validable = $validable;
  }

  /**
   * @return bool
   */
  public function isValid() {
    return !$this->validable->isValid();
  }

}
