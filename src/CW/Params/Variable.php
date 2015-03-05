<?php
/**
 * @file
 */

namespace CW\Params;

class Variable {

  const TYPE_SHORT_STRING = 'string';
  const TYPE_LONG_TEXT = 'text';

  protected $machineName;

  /**
   * @var null
   */
  protected $label;

  /**
   * @var null
   */
  protected $value;

  /**
   * @var null
   */
  protected $description;

  /**
   * @var string
   */
  private $type;

  public function __construct($machineName, $label = NULL, $description = NULL, $value = NULL, $type = self::TYPE_SHORT_STRING) {
    $this->machineName = $machineName;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->type = $type;
  }

  /**
   * @return mixed
   */
  public function getMachineName() {
    return $this->machineName;
  }

  /**
   * @param mixed $machineName
   */
  public function setMachineName($machineName) {
    $this->machineName = $machineName;
  }

  /**
   * @return null
   */
  public function getLabel() {
    return $this->label ? $this->label : $this->machineName;
  }

  /**
   * @param null $label
   */
  public function setLabel($label) {
    $this->label = $label;
  }

  /**
   * @return null
   */
  public function getValue() {
    return variable_get($this->getMachineName(), $this->value);
  }

  /**
   * @param null $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * @return null
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param null $description
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType($type) {
    $this->type = $type;
  }

}
