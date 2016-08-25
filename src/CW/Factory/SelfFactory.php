<?php
/**
 * @file
 */

namespace CW\Factory;

use ReflectionClass;

/**
 * Class SelfFactory
 * @package CW\Factory
 *
 * Simple trait to provide a self factory method. No arguments atm.
 */
trait SelfFactory {

  /**
   * Creates a new instance.
   * Mostly used to chain creations immediately: MyObject::createInstance()->foo().
   *
   * It accepts any number of arguments that it will pass to the concrete
   * constructor.
   *
   * @return static
   */
  public static function createInstance() {
    $selfClass = new ReflectionClass(static::class);
    return $selfClass->newInstanceArgs(func_get_args());
  }

}
