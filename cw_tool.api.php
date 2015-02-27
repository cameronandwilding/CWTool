<?php
/**
 * @file
 *
 * API.
 */

/**
 * Chance to add service.yml file folders to include to main page load.
 *
 * @param \CW\Util\SimpleList $collection
 */
function hook_cw_tool_service_container_definition_alter(\CW\Util\SimpleList $collection) {
  $collection->add('my/custom/path');
}
