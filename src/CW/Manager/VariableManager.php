<?php
/**
 * @file
 *
 * Variable manager.
 */

namespace CW\Manager;

use CW\Params\Variable;
use CW\Params\VariableGroup;
use CW\Util\LoggerObject;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class VariableManager
 * @package CW\Manager
 *
 * Variable manager collects variables, either "manually" or collects through
 * the global hook:
 *
 * @see hook_cw_tool_app_variables()
 */
class VariableManager extends LoggerObject {

  /**
   * @var Variable[]
   */
  protected $variables = array();

  /**
   * @var VariableGroup[]
   */
  protected $groups = array();

  /**
   * @var VariableGroup
   */
  private $defaultGroup;

  public function __construct(LoggerInterface $logger) {
    parent::__construct($logger);
    $this->defaultGroup = new VariableGroup(t('Generic application settings'));
    $this->addGroup($this->defaultGroup);
  }

  public function addGroup(VariableGroup $variableGroup) {
    $this->groups[] = $variableGroup;
  }

  /**
   * @param \CW\Params\Variable $variable
   */
  public function addVariable(Variable $variable) {
    $this->defaultGroup->addVariable($variable);
  }

  /**
   * @param Variable[] $variables
   */
  public function addVariables($variables) {
    foreach ($variables as $variable) {
      $this->addVariable($variable);
    }
  }

  /**
   * Collect all variables defined in hook_cw_tool_app_variables().
   *
   * @see hook_cw_tool_app_variables()
   *
   * @throws \Exception
   */
  public function collectAppVariables() {
    static $runCompletedFlag = FALSE;

    if ($runCompletedFlag) {
      throw new Exception('Variable manager app var collection has been run already.');
    }

    // @todo should be injected, eg IVariableCollector
    module_invoke_all('cw_tool_app_variables', $this);
    $runCompletedFlag = TRUE;
  }

  /**
   * @return \CW\Params\VariableGroup[]
   */
  public function getGroups() {
    return $this->groups;
  }

}
