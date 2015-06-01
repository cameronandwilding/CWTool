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
  public static function mapTranslate($map, $key, $not_found_value = NULL) {
    return array_key_exists($key, $map) ? $map[$key] : $not_found_value;
  }

  /**
   * Filters an array to a selection of keys.
   * It preserves the keys.
   *
   * @param array $array
   *  Input array with keys and values.
   * @param array $keys
   *  Keys to filter to.
   * @return array
   */
  public static function filterKeys(array $array, array $keys) {
    $out = array();
    foreach ($keys as $key) {
      $out[$key] = $array[$key];
    }
    return $out;
  }

  /**
   * Merge an array onto a primary array.
   *
   * @param array $array_primary
   * @param array $array_secondary
   */
  public static function merge(&$array_primary, $array_secondary) {
    $array_primary = array_merge($array_primary, $array_secondary);
  }
}
