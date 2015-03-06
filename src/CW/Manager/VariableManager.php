<?php
/**
 * @file
 */

namespace CW\Manager;

use CW\Params\Variable;
use CW\Util\LoggerObject;
use Exception;
use Psr\Log\LoggerInterface;

class VariableManager extends LoggerObject {

  /**
   * @var Variable[]
   */
  protected $variables = array();

  public function addVariable(Variable $variable) {
    $this->variables[] = $variable;
  }

  public function addVariables($variables) {
    foreach ($variables as $variable) {
      $this->addVariable($variable);
    }
  }

  public function getVariables() {
    return $this->variables;
  }

  public function collectAppVariables() {
    static $runCompletedFlag = FALSE;

    if ($runCompletedFlag) {
      throw new Exception('Variable manager app var collection has been run already.');
    }

    // @todo should be injected, eg IVariableCollector
    module_invoke_all('cw_tool_app_variables', $this);
    $runCompletedFlag = TRUE;
  }

}
