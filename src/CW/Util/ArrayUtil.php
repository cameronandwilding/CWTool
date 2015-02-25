<?php
/**
 * @file
 */

namespace CW\Util;

class ArrayUtil {

  public static function mapTranslate($map, $key, $not_found_value) {
    return array_key_exists($key, $map) ? $map[$key] : $not_found_value;
  }

}
