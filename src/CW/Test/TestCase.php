<?php
/**
 * @file
 *
 * Unit test case.
 */

namespace CW\Test;

use PHPUnit_Framework_TestCase;

/**
 * Class TestCase
 * @package CW\Test
 *
 * Unit test case wrapper.
 * At the moment the only purpose to provide some helpful methods.
 */
class TestCase extends PHPUnit_Framework_TestCase {

  /**
   * @param int $length
   * @return string
   */
  public static function randomString($length = 8) {
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;
    $str = '';
    for ($i = 0; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $str;
  }

  /**
   * @param int $max
   * @param int $min
   * @return int
   */
  public static function randomInt($max = PHP_INT_MAX, $min = 0) {
    return rand($min, $max);
  }

}
