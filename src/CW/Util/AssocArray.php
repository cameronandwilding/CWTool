<?php
/**
 * @file
 */

namespace CW\Util;

class AssocArray {

  private $array = [];

  public function __set($name, $value) {
    $this->array[$name] = $value;
  }

  public function __get($name) {
    return @$this->array[$name];
  }

  public function __isset($name) {
    return array_key_exists($name, $this->array);
  }

  public function has($name) {
    return $this->__isset($name);
  }

}
