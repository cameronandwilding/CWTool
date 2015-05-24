<?php
/**
 * @file
 *
 * Drupal variable adapter.
 */

namespace CW\Adapter;

/**
 * Class DrupalVariableAdapter
 * @package CW\Adapter
 *
 * Drupal adapter for variables.
 */
class DrupalVariableAdapter implements VariableAdapter {

  /**
   * {@inheritdoc}
   */
  public function get($key, $defaultValue = NULL) {
    return variable_get($key, $defaultValue);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    variable_set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    variable_del($key);
  }

}
