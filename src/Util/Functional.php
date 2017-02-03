<?php
/**
 * @file
 *
 * Functional flavored helpers.
 */

namespace Drupal\cw_tool\Util;

/**
 * Class Functional
 * @package Drupal\cw_tool\Util
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
      call_user_func($callable, $i);
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
   * Traverse a list and execute a callback for each element - using key and
   * value for arguments
   *
   * @param array $list
   * @param callable $callable
   *  Arguments:
   *    - key,
   *    - item.
   */
  public static function walkKeyValue($list, $callable) {
    foreach ($list as $key => $item) {
      call_user_func_array($callable, array($key, $item));
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
   * Mimics the functional dot: F1 . F2 ... Fn-1 . Fn -> Fn(Fn-1(...F2(F1())...)).
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

  /**
   * Creates an instance callable callback.
   *
   * @param string $callAttr
   *  Function name or property
   * @return \Closure
   */
  public static function selfCallFn($callAttr) {
    $args = func_get_args();
    array_shift($args);

    return function ($instance) use ($callAttr, $args) {
      if (is_object($instance)) {
        if (method_exists($instance, $callAttr)) {
          return call_user_func_array([$instance, $callAttr], $args);
        }
        else if (property_exists($instance, $callAttr)) {
          return $instance->{$callAttr};
        }
      }
      else if (is_array($instance)) {
        return $instance[$callAttr];
      }
      return $instance;
    };
  }

  /**
   * Provides the identity function that always returns whatever it is passed to.
   * @return \Closure
   */
  public static function id() {
    return function ($value = NULL) { return $value; };
  }

  public static function curry($callback) {
    $staticArgs = func_get_args();
    array_shift($staticArgs); // Remove the callback.

    return function () use ($callback, $staticArgs) {
      $extraArgs = func_get_args();
      $args = array_merge($staticArgs, $extraArgs);
      return call_user_func_array($callback, $args);
    };
  }

  /**
   * Generates a negating callback.
   *
   * @param callable $callable
   * @return \Closure
   */
  public static function fnNot($callable) {
    $staticArgs = func_get_args();
    array_shift($staticArgs);

    return function () use ($callable, $staticArgs) {
      $dynArgs = func_get_args();
      $args = array_merge($staticArgs, $dynArgs);

      return !call_user_func_array($callable, $args);
    };
  }

}
