<?php
/**
 * @file
 */

function cw_tool_test_autoloader($className) {
  $parts = explode('\\', $className);
  if ($parts[0] == 'Drupal' && $parts[1] == 'cw_tool') {
    array_shift($parts);
    array_shift($parts);
    $classTopName = array_pop($parts);
    require_once __DIR__ . '/../../src/' . implode('/', $parts) . '/' . $classTopName . '.php';
  }

  return TRUE;
}
spl_autoload_register('cw_tool_test_autoloader');

// Composer.
require_once __DIR__ . '/../../vendor/autoload.php';

// Fixtures.
require_once __DIR__ . '/fixtures/MinimalEntityController.php';
