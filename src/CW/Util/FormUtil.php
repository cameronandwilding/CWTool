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

  // Available hooks to call.
  const HOOK_SUBMIT = '#submit';
  const HOOK_VALIDATE = '#validate';
  const HOOK_AFTER_BUILD = '#after_build';

  // Storage key in the form array for FormUtil.
  const FORM_HOOK_KEY = '#cw_tool';

  /**
   * Register a form callback used in Drupal (submit, validate, after build).
   *
   * @param array $form
   * @param string $hook
   *  self::HOOK_*
   * @param string $callback
   */
  protected static function registerCallback(&$form, $hook, $callback) {
    if (!isset($form[$hook])) {
      $form[$hook] = array();
    }

    $cw_tool_callback = 'cw_tool_form_util_' . str_replace('#', '', $hook);
    if (!in_array($cw_tool_callback, $form[$hook])) {
      $form[$hook][] = $cw_tool_callback;
    }

    if (!isset($form[self::FORM_HOOK_KEY][$hook])) {
      $form[self::FORM_HOOK_KEY][$hook] = array();
    }

    if (!in_array($callback, $form[self::FORM_HOOK_KEY][$hook], TRUE)) {
      $form[self::FORM_HOOK_KEY][$hook][] = $callback;
    }
  }

  /**
   * Execute hooks registered in the form by FormUtil.
   *
   * @param string $hook
   *  self::HOOK_*
   * @param array $form
   * @param array $form_state
   */
  public static function callHook($hook, &$form, &$form_state) {
    if (!isset($form[self::FORM_HOOK_KEY][$hook])) {
      return;
    }

    foreach ($form[self::FORM_HOOK_KEY][$hook] as $callback) {
      call_user_func_array($callback, array(&$form, &$form_state));
    }
  }

  /**
   * Execute registered after build callbacks.
   *
   * @param array $form
   * @param array $form_state
   * @return array
   */
  public static function callAfterBuildHook($form, &$form_state) {
    if (!isset($form[self::FORM_HOOK_KEY][self::HOOK_AFTER_BUILD])) {
      return $form;
    }

    foreach ($form[self::FORM_HOOK_KEY][self::HOOK_AFTER_BUILD] as $callback) {
      $form = call_user_func_array($callback, array(&$form, &$form_state));
    }

    return $form;
  }

  /**
   * Registers a validation callback for the form.
   *
   * @param array $form
   * @param string $callback
   */
  public static function registerValidationCallback(&$form, $callback) {
    self::registerCallback($form, self::HOOK_VALIDATE, $callback);
  }

  /**
   * Registers a submit callback for the form.
   *
   * @param array $form
   * @param string $callback
   */
  public static function registerSubmitCallback(&$form, $callback) {
    self::registerCallback($form, self::HOOK_SUBMIT, $callback);
  }

  /**
   * Registers an after build callback for the form.
   *
   * @param array $form
   * @param string $callback
   */
  public static function registerAfterBuildCallback(&$form, $callback) {
    self::registerCallback($form, self::HOOK_AFTER_BUILD, $callback);
  }

  /**
   * @param array $form
   * @param string $field_name
   * @return string
   */
  public static function getFieldLabel(&$form, $field_name) {
    if (!isset($form[$field_name][$form[$field_name]['#language']]['#title'])) {
      return NULL;
    }

    return $form[$field_name][$form[$field_name]['#language']]['#title'];
  }

}
