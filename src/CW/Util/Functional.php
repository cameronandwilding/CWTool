<?php
/**
 * @file
 *
 * Functional flavored helpers.
 */

namespace CW\Util;

/**
 * Class Functional
 * @package CW\Util
 */
class Functional {

  /**
   * Execute something X times.
   *
   * @param int $count
   * @param callable $callable
   */
  public static function times($count, $callable) {
    for ($i = 0; $i < $count; $i++) {
      call_user_func($callable);
    }
  }

  /**
   * Traverse a list and execute a callback for each element.
   *
   * @param array $list
   * @param callable $callable
   *  Arguments:
   *    - item,
   *    - key.
   */
  public static function walk($list, $callable) {
    foreach ($list as $key => $item) {
      call_user_func_array($callable, array($item, $key));
    }
  }

}
