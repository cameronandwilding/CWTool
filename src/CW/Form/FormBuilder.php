<?php
/**
 * @file
 *
 * Form builder.
 */

namespace CW\Form;

use CW\Exception\MissingImplementationException;
use CW\Util\FormUtil;

/**
 * Class FormBuilder
 * @package CW\Form
 *
 * Used for creating custom forms.
 */
abstract class FormBuilder {

  /**
   * Get the form in a render-able format (render() has to be called on it still).
   *
   * @return array
   */
  public static function get() {
    $args = func_get_args();
    return drupal_get_form('cw_tool_form:' . get_called_class(), $args);
  }

  /**
   * Attach submit and validation handlers.
   *
   * @param array $form
   */
  public static function attachSubmitAndValidation(&$form) {
    FormUtil::registerSubmitCallback($form, array(get_called_class(), 'submit'));
    FormUtil::registerValidationCallback($form, array(get_called_class(), 'validate'));
  }

  /**
   * Main form generation.
   * Add additional arguments as optional arguments (= NULL).
   * Use the $form array provided by the function to attach the elements on.
   *
   * @param array $form
   * @param array $form_state
   * @return array
   *  Form array.
   * @throws \CW\Exception\MissingImplementationException
   */
  public static function build($form, $form_state) {
    throw new MissingImplementationException('You have to implement the static build hook to provide the form array.');
  }

  /**
   * Submit hook.
   *
   * @param array $form
   * @param array $form_state
   */
  public static function submit(&$form, &$form_state) {
    // Implement in your concrete class.
  }

  /**
   * Validation hook.
   *
   * @param array $form
   * @param array $form_state
   */
  public static function validate($form, $form_state) {
    // Implement in your concrete class.
  }

}
