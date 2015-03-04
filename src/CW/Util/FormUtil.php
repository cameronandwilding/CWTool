<?php
/**
 * @file
 */

namespace CW\Util;

class FormUtil {

  public static function registerAfterBuild(&$form, $hook) {
    if (!isset($form['#after_build']) || !is_array($form['#after_build'])) {
      $form['#after_build'] = array();
    }
    $form['#after_build'][] = $hook;
  }

}
