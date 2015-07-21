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

  /**
   * Merge a collection of array items onto an array.
   *
   * @param array $original_array
   * @param array $collection
   */
  public static function mergeCollection(array &$original_array, array $collection) {
    foreach ($collection as $list) {
      self::merge($original_array, $list);
    }
  }

  /**
   * Convert a multiline string to an array, which line as a value of the array.
   *
   * @param string $string
   * @return array
   */
  public static function multiLineStringToArray($string) {
    return explode(PHP_EOL, $string);
  }

  /**
   * Convert a multiline string to an array, with each line trimmed.
   *
   * @param string $string
   * @param string $character_mask
   * @return array
   */
  public static function multiLineStringToArrayAndTrimValues($string, $character_mask = " \t\n\r\0\x0B") {
    $array = self::multiLineStringToArray($string);
    foreach ($array as &$value) {
      $value = trim($value, $character_mask);
    }
    return $array;
  }
}
