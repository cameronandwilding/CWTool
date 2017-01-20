<?php
/**
 * @file
 */

namespace CW\Drush;

/**
 * Class DrushDefinition
 *
 * @package CW\Drush
 *
 * Drush command definition.
 */
class DrushDefinition {

  // Commands.
  const COMMAND_SCAFFOLD_ENTITY_CONTROLLER = 'cwtool-scaffold-entity-controller';

  /**
   * Implements hook_drush_command().
   *
   * @return array
   */
  public static function commandInfo() {
    return [
      self::COMMAND_SCAFFOLD_ENTITY_CONTROLLER => [
        'description' => 'Creates a boilerplate entity controller.',
        'arguments' => [
          'entity_type' => 'Entity type (node, user, file, taxonomy_term, field_collection)',
          'bundle' => 'Bundle',
        ],
        'options' => [
          'namespace' => [
            'description' => 'Namespace for the controller class.',
            'example-value' => 'Corp\\Vendor',
          ],
        ],
        'examples' => [
          'basic' => 'drush cwt-sc-ctrl node blog',
        ],
        'aliases' => [
          'cwt-sc-ctrl',
        ],
      ],
    ];
  }

}
