<?php
/**
 * @file
 */

namespace CW\Test;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase {

  public static function randomString($length = 8) {
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;
    $str = '';
    for ($i = 0; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $str;
  }

  public static function randomInt($max = PHP_INT_MAX, $min = 0) {
    return rand($min, $max);
  }

}
