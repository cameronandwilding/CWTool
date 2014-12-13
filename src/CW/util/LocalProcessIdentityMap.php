<?php
/**
 * @file
 *
 * Identity map.
 */

namespace CW\Util;

use CW\Exception\IdentityMapException;

/**
 * Class LocalProcessIdentityMap
 * @package CW\Util
 *
 * Implements identity map pattern for a single PHP process (~static cache).
 */
class LocalProcessIdentityMap {

  /**
   * Container for items.
   *
   * @var array
   */
  private $map = array();

  /**
   * Check if key exist for any items.
   *
   * @param string $key
   *  Key to check.
   * @return bool
   */
  public function keyExist($key) {
    return array_key_exists($key, $this->map);
  }

  /**
   * Add new item.
   *
   * @param string $key
   * @param mixed $data
   * @throws IdentityMapException
   */
  public function add($key, $data) {
    if ($this->keyExist($key)) {
      throw new IdentityMapException('Key exist: ' . $key);
    }

    $this->map[$key] = $data;
  }

  /**
   * Get item.
   *
   * @param string $key
   * @return mixed
   * @throws IdentityMapException
   */
  public function get($key) {
    if (!$this->keyExist($key)) {
      throw new IdentityMapException('Key does not exist: ' . $key);
    }

    return $this->map[$key];
  }

  /**
   * Delete all items.
   */
  public function deleteAll() {
    $this->map = array();
  }

  /**
   * Delete single item.
   *
   * @param string $key
   */
  public function delete($key) {
    unset($this->map[$key]);
  }

}
