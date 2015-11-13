<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\UtilityCollectionInterface;
use CW\Factory\SelfFactory;

/**
 * Class BasicUtilityCollection
 *
 * @package CW\Util
 *
 * Basic utility functions.
 * For explanation to utility collections:
 * @see CW\Adapter\UtilityCollectionInterface
 */
class BasicUtilityCollection implements UtilityCollectionInterface {

  use SelfFactory;

  /**
   * @param int $length
   * @return string
   */
  public function randomString($length = 8) {
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
  public function randomInt($max = PHP_INT_MAX, $min = 0) {
    return rand($min, $max);
  }

}
