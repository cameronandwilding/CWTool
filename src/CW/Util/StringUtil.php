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
}
