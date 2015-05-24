<?php
/**
 * @file
 */

namespace CW\Structure;

trait Singleton {

  private static $__instance;

  public static function getInstance() {
    if (empty(self::$__instance)) {
      self::$__instance = new static();
    }
    return self::$__instance;
  }

}
