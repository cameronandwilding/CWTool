<?php
/**
 * @file
 *
 * Array utility functions.
 */

namespace CW\Util;

/**
 * Class ArrayUtil
 * @package CW\Util
 */
class ArrayUtil {

  /**
   * Map translate helps to translate one value to another using a translation
   * map.
   * Returns NULL if it doesn't exist.
   *
   * For example as a language provider:
   * @code
   * $dict = [
   *  'developer' => '開発者',
   *  'rabbit'    => 'ウサギ',
   * ];
   * ArrayUtil::mapTranslate($dict, 'developer'); // Return: 開発者.
   * ArrayUtil::mapTranslate($dict, 'Jennifer'); // Return: (NULL).
   * @endcode
   *
   * @param $map
   * @param $key
   * @param $not_found_value
   * @return mixed
   */
  public static function mapTranslate($map, $key, $not_found_value) {
    return array_key_exists($key, $map) ? $map[$key] : $not_found_value;
  }

}
