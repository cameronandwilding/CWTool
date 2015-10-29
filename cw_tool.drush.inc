<?php
/**
 * @file
 *
 * Drush commands.
 */

use CW\Drush\Command\CommandScaffoldEntityController;
use CW\Drush\DrushDefinition;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Implements hook_drush_command().
 */
function cw_tool_drush_command() {
  return DrushDefinition::commandInfo();
}

/**
 * Action callback for entity controller scaffolding command.
 *
 * @param string $entityType
 * @param string $bundle
 */
function drush_cw_tool_cwtool_scaffold_entity_controller($entityType, $bundle) {
  CommandScaffoldEntityController::execute($entityType, $bundle);
}
