<?php
/**
 * @file
 */

namespace CW\Factory;

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
   * @return static
   */
  public static function createInstance() {
    return new static();
  }

}
