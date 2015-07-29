<?php
/**
 * @file
 *
 * String util.
 */

namespace CW\Util;
use CW\Exception\CWException;
use IntelligentLife\Core\Exception\IntelligentLifeException;

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
   * @param string $haystack
   * @param string $needle
   * @return bool
   */
  public static function stringEndsWith($haystack, $needle) {
    $offset = drupal_strlen($haystack) - drupal_strlen($needle);
    return strpos($haystack, $needle, $offset) !== FALSE;
  }

  /**
   * Make path (in URL) compatible string that contains only lower case letters,
   * digits and underscore.
   *
   * @param string $string
   * @return string
   */
  public static function pathEncode($string) {
    return mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $string));
  }

  /**
   * Insert string into string at given position.
   *
   * @param string $haystack
   *  Original string.
   * @param string $needle
   *  Insertion.
   * @param int $position
   *  This is the byte number, it handles multibyte strings as multiple positions.
   *  This means you shouldn't add a position which is inside a multibyte char.
   * @return string
   * @throws \CW\Exception\CWException
   */
  public static function insertTo($haystack, $needle, $position) {
    $length = strlen($haystack);
    if ($position < 0) {
      $position = $length + $position;
    }

    if ($position < 0 || $position > $length) {
      throw new CWException('Position is outside of the string: ' . $position);
    }

    $left = substr($haystack, 0, $position);
    $right = substr($haystack, $position);
    return $left . $needle . $right;
  }

}
