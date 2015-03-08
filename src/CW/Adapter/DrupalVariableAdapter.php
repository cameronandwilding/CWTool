<?php
/**
 * @file
 */

namespace CW\Adapter;

class DrupalVariableAdapter implements VariableAdapter {

  public function get($key, $defaultValue = NULL) {
    return variable_get($key, $defaultValue);
  }

  public function set($key, $value) {
    variable_set($key, $value);
  }

  public function delete($key) {
    variable_del($key);
  }

}
