<?php
/**
 * @file
 *
 * Variable abstraction.
 */

namespace CW\Params;

/**
 * Class Variable
 * @package CW\Params
 */
class Variable {

  // Variable types.
  const TYPE_SHORT_STRING = 'string';
  const TYPE_LONG_TEXT = 'text';
  const TYPE_FORMATTED_TEXT = 'formatted';

  /**
   * @var string
   */
  protected $machineName;

  /**
   * @var string
   */
  protected $label;

  /**
   * @var mixed
   */
  protected $value;

  /**
   * @var string
   */
  protected $description;

  /**
   * @var string
   */
  private $type;

  /**
   * @param $machineName
   * @param string $label
   * @param string $description
   * @param mixed $value
   * @param string $type
   */
  public function __construct($machineName, $label = NULL, $description = NULL, $value = NULL, $type = self::TYPE_SHORT_STRING) {
    $this->machineName = $machineName;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->type = $type;
  }

  /**
   * @return string
   */
  public function getMachineName() {
    return $this->machineName;
  }

  /**
   * @param string $machineName
   */
  public function setMachineName($machineName) {
    $this->machineName = $machineName;
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label ? $this->label : $this->machineName;
  }

  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
  }

  /**
   * @return mixed
   */
  public function getValue() {
    $value = variable_get($this->getMachineName(), $this->value);

    // When it's a formatted text, the value is an array with the format type.
    // We need to extract the value.
    // @todo Known issue is that the format type (eg full_html) is not communicated to the form.
    if ($this->getType() == self::TYPE_FORMATTED_TEXT) {
      return $value['value'];
    }

    return $value;
  }

  /**
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param string $description
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
