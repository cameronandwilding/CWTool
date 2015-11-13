<?php
/**
 * @file
 */

namespace CW\Util;

/**
 * Class AssocArray
 *
 * @package CW\Util
 *
 * Wrapper class for associative arrays to provide convenience methods.
 */
class AssocArray {

  /**
   * @var array
   */
  private $array = [];

  /**
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    $this->array[$name] = $value;
  }

  /**
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    return @$this->array[$name];
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    return array_key_exists($name, $this->array);
  }

  /**
   * @param string $name
   * @return bool
   */
  public function has($name) {
    return $this->__isset($name);
  }

}
