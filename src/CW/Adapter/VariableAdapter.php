<?php
/**
 * @file
 */

namespace CW\Adapter;

interface VariableAdapter {

  public function get($key, $defaultValue = NULL);

  public function set($key, $value);

  public function delete($key);

}
