<?php
/**
 * @file
 *
 * List data type.
 */

namespace Drupal\cw_tool\Util;

/**
 * Class SimpleList
 *
 * To be used as a parameter where a list is built.
 *
 * @package Drupal\cw_tool\Util
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
