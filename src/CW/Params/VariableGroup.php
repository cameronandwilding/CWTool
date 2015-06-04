<?php
/**
 * @file
 *
 * Variable groups are to group variables for the application into different
 * responsibilities.
 */

namespace CW\Params;

/**
 * Class VariableGroup
 * @package CW\Params
 */
class VariableGroup {

  /**
   * @var string
   */
  private $title;

  /**
   * @var Variable[]
   */
  private $variables = array();

  /**
   * @var string
   */
  private $machineName;

  /**
   * @var int
   */
  private static $counter = 0;

  /**
   * @param string $title
   */
  public function __construct($title) {
    $this->title = $title;
    $this->machineName = 'group_' . self::$counter;
    self::$counter++;
  }

  /**
   * @param \CW\Params\Variable $variable
   */
  public function addVariable(Variable $variable) {
    $this->variables[] = $variable;
  }

  /**
   * @param Variable[] $variables
   */
  public function addVariables(array $variables = array()) {
    foreach ($variables as $variable) {
      $this->addVariable($variable);
    }
  }

  /**
   * @return Variable[]
   */
  public function getVariables() {
    return $this->variables;
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
  public function getTitle() {
    return $this->title;
  }

  /**
   * @return bool
   */
  public function hasVariable() {
    return count($this->getVariables()) > 0;
  }

}
