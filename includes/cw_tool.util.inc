<?php
/**
 * @file
 *
 * Generic utility helpers.
 */

/**
 * Checks if a value exist (for a given key) in a multidimensional array.
 *
 * Example usage: check a value in a field array:
 * Haystack:
 * [
 *  0: [value: 123, format: decimal],
 *  1: [value: 456, format: decimal],
 * ]
 * Value key: 'value'
 * Needle: 456
 * Return: TRUE
 *
 * @param array $haystack
 *  An array of keys from an entity object.
 * @param $value_key
 *  The key of the value.
 * @param $needle
 *  The value to look for.
 *
 * @return bool
 *  Boolean flag if the value is in the multidimensional-array.
 */
function cw_tool_check_value_in_multidimensional_array(array $haystack, $value_key, $needle) {
  array_walk($haystack, function (&$item, $idx, $key) {
    $item = isset($item[$key]) ? $item[$key] : NULL;
  }, $value_key);
  return in_array($needle, $haystack);
}

/**
 * Reset features cache and revert the features.
 * Useful when you want to use the component the same time you enabled the feature (eg in the same update hook).
 * In that case the component is still not created (queued) so you need to trigger the creation manually.
 *
 * @param string $featureName
 *  The feature to be reset and reverted.
 * @param string[] $components
 *  The feature component to be reverted.
 */
function cw_tool_revert_feature($featureName, $components) {
  // Reset features cache.
  features_get_info('feature', $featureName, TRUE);

  // Revert taxonomy defined by the feature.
  features_revert(array($featureName => $components));
}

/**
 * Converts a bitmap file to png.
 *
 * @param $file
 *  The file that needs to be converted to png.
 * @param $type
 *  The type of the file that needs to be converted to png.
 *
 * @return bool
 *  TRUE if the file was successfully converted to PNG, FALSE otherwise.
 */
function cw_tool_convert_to_png($file, $type) {
  // Load the object of the file we want to convert
  $basename = basename($file->uri, $type);
  $new_uri = drupal_dirname($file->uri) . '/' . $basename . '.png';
  // Try converting the file to png with imagemagick
  $dest = _imagemagick_convert($file->uri, $new_uri, array());
  if (!$dest) {
    watchdog(__FUNCTION__, 'Imagemagick failed to convert the uploaded file to png.', array(), WATCHDOG_WARNING);
    return FALSE;
  }

  $file->uri = $new_uri;
  $file->filename = $basename . '.png';
  $file->filemime = 'image/png';
  file_save($file);

  watchdog(__FUNCTION__, 'Imagemagick converted uploaded file to png.');
  return TRUE;
}

/**
 * Wrapper for watchdog to handle multiple variable logging.
 *
 * @param $scope
 *  Original scope - __FUNCTION__, __METHOD__ or similar.
 * @param $values
 *  The key - value array of values that will be presented in the message.
 * @param $severity
 *  (optional) The severity of the error - watchdog error level code.
 * @param $message
 *  (optional) Message string.
 */
function cw_tool_var_watchdog($scope, array $values, $severity = WATCHDOG_WARNING, $message = 'CW watchdog') {
  $arguments = array();
  foreach ($values as $key => $value) {
    // To be able to check objects we convert them to arrays.
    if (is_object($value)) {
      $value = (array) $value;
    }
    // Flattening arrays into strings.
    if (is_array($value)) {
      $value = '(string value)' . preg_replace("/[\n\t\r ]/m", '', var_export($value, TRUE));
    }
    $arguments[':' . $key] = str_replace(array("\n", "\r"), '{line-break}', $value);
    $message .= " $key=(:$key)";
  }

  watchdog($scope, $message, $arguments, $severity);
}

/**
 * Provides a debugging backtrace with function name and file name.
 */
function cw_tool_print_backtrace() {
  $backtrace = array_map('_cw_tool_parse_function_name', debug_backtrace());
  print_r($backtrace);
}

/**
 * Returns the function and file name of each element in an array.
 *
 * @param $element
 * @return string
 */
function _cw_tool_parse_function_name($element) {
  return $element['function'] . ' >> ' . $element['file'];
}

/**
 * Does a case insensitive search for a file.
 *
 * @see http://stackoverflow.com/questions/3964793/php-case-insensitive-version-of-file-exists
 *
 * @param $file_name
 *  The file name (including path) that we're checking exists.
 *
 * @return bool $file
 *  The path to the file name (with correct case), or FALSE if the file doesn't
 *  exist.
 */
function cw_tool_file_exists_case_insensitive($file_name) {
  if (file_exists($file_name)) {
    return $file_name;
  }

  // Handle case insensitive requests.
  // glob doesn't support file stream wrappers like private://, so we need to
  // call the drupal_realpath function.
  $directory_name = drupal_realpath(dirname($file_name));
  $files = glob($directory_name . '/*', GLOB_NOSORT);
  $file_name_lower_case = drupal_strtolower(basename($file_name));
  foreach ($files as $file) {
    if (drupal_strtolower(basename($file)) == $file_name_lower_case) {
      return $file;
    }
  }
  return FALSE;
}

/**
 * Save theme color settings.
 *
 * @param string $theme
 *  The name of the theme we are saving color settings to.
 * @param string $scheme
 *  The name of the color scheme we are saving, or custom to save a custom scheme.
 * @param array $palette
 *  A keyed array of custom colour values if saving a custom color scheme.
 */
function cw_tool_save_theme_color_settings($theme, $scheme, array $palette = array()) {
  if (!module_exists('color')) {
    watchdog(__FUNCTION__, 'Module \'color\' is not enabled.', array(), WATCHDOG_ERROR);
    return;
  }

  // If we are saving a custom color scheme, we need a palette.
  if ($scheme == 'custom' && empty($palette)) {
    watchdog(__FUNCTION__, 'A palette must be provided when saving a custom color scheme.', array(), WATCHDOG_ERROR);
    return;
  }
  // Load the color info.
  $info = color_get_info($theme);
  // The color module requires a default scheme defined to replace colours.
  if (!isset($info['schemes']['default']['colors'])) {
    watchdog(__FUNCTION__, 'No default palette defined for theme @theme_name.', array('@theme_name' => $theme), WATCHDOG_ERROR);
    return;
  }
  // If we are not saving a custom scheme, check that the scheme is defined.
  if ($scheme != 'custom' && !isset($info['schemes'][$scheme])) {
    watchdog(__FUNCTION__, 'Color scheme @scheme_name not defined by the theme @theme_name.', array(
      '@scheme_name' => $scheme,
      '@theme_name' => $theme,
    ), WATCHDOG_ERROR);
    return;
  }
  // If we have an empty palette, we have a defined scheme, so load default to
  // compare.
  $palette = empty($palette) ? $info['schemes']['default']['colors'] : $palette;
  // When scheme is custom, color module expects an empty string.
  $scheme = $scheme == 'custom' ? '' : $scheme;
  // Save color settings.
  $form_state = array(
    'values' => array(
      'theme' => $theme,
      'info' => $info,
      'palette' => $palette,
      'scheme' => $scheme,
    ),
  );
  // Save color settings.
  module_load_include('inc', 'system', 'system.admin');
  $form = drupal_get_form('system_theme_settings', $theme);
  color_scheme_form_submit($form, $form_state);
}

/**
 * Converts a human readable string into a machine readable string.
 * Using dashes (not underscore).
 *
 * @param string $human_readable
 *  Any string.
 * @return string
 */
function cw_tool_create_machine_name($human_readable) {
  $machine_readable = strtolower($human_readable);
  // @todo Default to hyphen but perhaps you might want an underscore.
  $machine_readable = preg_replace('@[^a-z0-9_]+@', '-', $machine_readable);
  return $machine_readable;
}
