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

  /**
   * Apply a function to all elements and returns TRUE if any evaluation is TRUE.
   *
   * @param array $subjects
   * @param $callable
   * @return bool
   */
  public static function any(array $subjects, $callable) {
    foreach ($subjects as $subject) {
      if (call_user_func($callable, $subject)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Apply a function to all elements and returns TRUE if all evaluation is TRUE.
   *
   * @param array $subjects
   * @param $callable
   * @return bool
   */
  public static function all(array $subjects, $callable) {
    foreach ($subjects as $subject) {
      if (!call_user_func($callable, $subject)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Calls a function on each element of an array and return the first non
   * empty result.
   *
   * @param array $list
   * @param callable $callable
   *  Signature:
   *    - param: value of current element in iteration
   *    - param: key of current element in iteration
   * @param mixed $defaultValue In case none of the evaluations give result.
   *
   * @return mixed
   */
  public static function first(array $list, $callable, $defaultValue = NULL) {
    foreach ($list as $key => $value) {
      $return = call_user_func($callable, $value, $key);
      if (!empty($return)) {
        return $return;
      }
    }
    return $defaultValue;
  }

  /**
   * Mimics the functional dot: F1 . F2 . F3 .. -> F1(F2(F3(...))).
   *
   * @param callable[] $callbacks
   * @param mixed $value
   * @return mixed
   */
  public static function dot(array $callbacks = [], $value) {
    foreach ($callbacks as $callback) {
      $value = call_user_func($callback, $value);
    }
    return $value;
  }

}
