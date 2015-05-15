<?php
/**
 * @file
 *
 * Theme handler.
 */

namespace CW\Theme;

use CW\Exception\CWException;

/**
 * Class Theme
 * @package CW\Theme
 */
abstract class Theme {

  /**
   * Return rendered output.
   *
   * @return string
   * @throws \CW\Exception\CWException
   * @throws \Exception
   */
  final public function render() {
    return theme(static::getName(), $this->getVariables());
  }

  /**
   * Get template variables.
   *
   * @return array
   */
  abstract public function getVariables();

  /**
   * Machine readable name.
   *
   * @return string
   * @throws \CW\Exception\CWException
   */
  public static function getName() {
    throw new CWException('Missing implementation');
  }

  /**
   * Hook theme definition array content.
   *
   * @return array
   * @throws \CW\Exception\CWException
   */
  protected static function getDefinition() {
    throw new CWException('Missing implementation');
  }

  /**
   * Return the keyed array for hook_theme().
   *
   * @return array
   * @throws \CW\Exception\CWException
   */
  final public static function getHookThemeArray() {
    return array(static::getName() => static::getDefinition());
  }

}
