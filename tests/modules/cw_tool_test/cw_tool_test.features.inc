<?php
/**
 * @file
 * cw_tool_test.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function cw_tool_test_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_node_info().
 */
function cw_tool_test_node_info() {
  $items = array(
    'cwtoolbundle' => array(
      'name' => t('cwtoolbundle'),
      'base' => 'node_content',
      'description' => t('cwtoolbundle'),
      'has_title' => '1',
      'title_label' => t('title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
