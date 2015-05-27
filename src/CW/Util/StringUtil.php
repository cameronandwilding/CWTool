<?php
/**
 * @file
 *
 * String util.
 */

namespace CW\Util;

/**
 * Class StringUtil
 * @package CW\Util
 */
class StringUtil {

  /**
   * @param string $string
   * @return mixed
   */
  public static function snakeCase($string) {
    return preg_replace('/[^a-zA-Z0-9]/', '_', $string);
  }

  /**
   * Check that a string ends with another string.
   *
   * @param string $string
   * @param string $needle
   * @return bool
   */
  public static function stringEndsWith($string, $needle) {
    $offset = strlen($string) - strlen($needle);
    return strpos($string, $needle, $offset) !== FALSE;
  }

  /**
   * Remove the last character from a string.
   *
   * @param string $string
   * @return string
   */
  public static function removeLastCharacterFromString($string) {
    return substr($string, 0, -1);
  }
}
