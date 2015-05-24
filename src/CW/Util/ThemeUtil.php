<?php
/**
 * @file
 *
 * Theme utils.
 */

namespace CW\Util;

/**
 * Class ThemeUtil
 * @package CW\Util
 */
class ThemeUtil {

  /**
   * Generate the link part of a bookmark.
   *
   * @param string $name
   * @param string $content
   * @param array $attributes
   *  Key value array of HTML tag attributes.
   * @return string
   *  HTML.
   */
  public static function bookmarkLink($name, $content, $attributes = array()) {
    return '<a href="#' . $name . '"' . drupal_attributes($attributes) . '>' . $content . '</a>';
  }

  /**
   * Generate the anchor part of the bookmark.
   *
   * @param string $name
   * @param string $content
   * @param array $attributes
   *  Key value array of HTML tag attributes.
   * @return string
   *  HTML.
   */
  public static function bookmarkAnchor($name, $content = '', $attributes = array()) {
    return '<a name="' . $name . '"' . drupal_attributes($attributes) . '>' . $content . '</a>';
  }

}
