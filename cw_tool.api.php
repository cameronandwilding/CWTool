<?php
/**
 * @file
 *
 * API.
 */

/**
 * Chance to add service.yml file folders to include to main page load.
 * The yml file name expected to be: services.yml
 *
 * @param \CW\Util\SimpleList $collection
 */
function hook_cw_tool_service_container_definition_alter(\CW\Util\SimpleList $collection) {
  $collection->add('my/custom/path');
}

/**
 * Collects application variables.
 *
 * @param \CW\Manager\VariableManager $variableManager
 */
function hook_cw_tool_app_variables(\CW\Manager\VariableManager $variableManager) {
  $variableManager->addVariable(new \CW\Params\Variable('myVar', 'My variable'));
}
