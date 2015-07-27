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

  /**
   * Creates a cached version of the function.
   *
   * @param callable $callback
   * @return callable
   */
  public static function memoize($callback) {
    return function () use ($callback) {
      static $cached = NULL;

      if (empty($cached)) {
        $cached = call_user_func($callback);
      }

      return $cached;
    };
  }

  /**
   * Apply a function on each element in the list.
   *
   * @param array $list
   * @param callable $callback
   */
  public static function apply(array &$list, $callback) {
    array_walk($list, function (&$item) use ($callback) {
      $item = call_user_func($callback, $item);
    });
  }

}
