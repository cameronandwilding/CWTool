<?php
/**
 * @file
 *
 * Variable adapter.
 */

namespace CW\Adapter;

/**
 * Interface VariableAdapter
 * @package CW\Adapter
 *
 * Responsible for loading/updating/deleting application variables.
 */
interface VariableAdapter {

  /**
   * @param string $key
   * @param mixed $defaultValue
   * @return mixed
   */
  public function get($key, $defaultValue = NULL);

  /**
   * @param string $key
   * @param mixed $value
   */
  public function set($key, $value);

  /**
   * @param string $key
   */
  public function delete($key);

}
