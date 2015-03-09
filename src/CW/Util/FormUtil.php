<?php
/**
 * @file
 *
 * Form util.
 */

namespace CW\Util;

/**
 * Class FormUtil
 * @package CW\Util
 *
 * Form helpers.
 */
class FormUtil {

  /**
   * @param array $form
   * @param string $hook
   */
  public static function registerAfterBuild(&$form, $hook) {
    if (!isset($form['#after_build']) || !is_array($form['#after_build'])) {
      $form['#after_build'] = array();
    }
    $form['#after_build'][] = $hook;
  }

}
