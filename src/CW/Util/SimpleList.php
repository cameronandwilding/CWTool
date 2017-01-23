<?php
/**
 * @file
 *
 * List data type.
 */

namespace CW\Util;

/**
 * Class SimpleList
 *
 * To be used as a parameter where a list is built.
 *
 * @package CW\Util
 */
class SimpleList {

  /**
   * @var array
   */
  protected $items = array();

  /**
   * @param mixed $item
   */
  public function add($item) {
    $this->items[] = $item;
  }

  /**
   * @return mixed[]
   */
  public function getAll() {
    return $this->items;
  }

}
