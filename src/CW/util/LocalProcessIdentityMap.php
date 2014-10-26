<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Exception\IdentityMapException;

class LocalProcessIdentityMap {

  private $map = array();

  public function keyExist($key) {
    return array_key_exists($key, $this->map);
  }

  public function add($key, $data) {
    if ($this->keyExist($key)) {
      throw new IdentityMapException('Key exist: ' . $key);
    }

    $this->map[$key] = $data;
  }

  public function get($key) {
    if (!$this->keyExist($key)) {
      throw new IdentityMapException('Key does not exist: ' . $key);
    }

    return $this->map[$key];
  }

  public function deleteAll() {
    $this->map = array();
  }

  public function delete($key) {
    unset($this->map[$key]);
  }

}
