<?php
/**
 * @file
 */

namespace CW\Structure;

/**
 * Class Singleton
 * @package CW\Structure
 */
abstract class Singleton {

  /**
   * @var static
   */
  private static $instance;

  /**
   * Constructor.
   */
  protected function __construct() { }

  /**
   * @return static
   */
  public static function getInstance() {
    if (empty(self::$instance)) {
      self::$instance = new static();
    }
    return self::$instance;
  }

}
