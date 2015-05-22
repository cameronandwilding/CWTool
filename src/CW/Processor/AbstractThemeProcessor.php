<?php
/**
 * @file
 * Abstract theme processor.
 */

namespace CW\Processor;

use CW\Exception\ThemeException;
use itarato\VarCheck\VC;

/**
 * Class AbstractThemeProcessor
 * @package CW\Processor
 */
abstract class AbstractThemeProcessor {

  /**
   * @var array
   */
  private $vars;

  /**
   * @param array $vars
   */
  public function __construct(&$vars) {
    $this->vars = &$vars;
    if ($this->isApplicable()) {
      $this->execute();
    }
  }

  /**
   * @param string $key
   * @return mixed
   * @throws ThemeException
   */
  protected function getVar($key) {
    if (!$this->hasVar($key)) {
      throw new ThemeException('Missing item in theme vars: ' . $key);
    }
    return $this->vars[$key];
  }

  /**
   * @return VC
   */
  protected function getWrappedVar() {
    return VC::make($this->vars);
  }

  /**
   * @param string $key
   * @param mixed $value
   */
  protected function setVar($key, $value) {
    $this->vars[$key] = $value;
  }

  /**
   * @param string $key
   * @param mixed $value
   * @throws ThemeException
   */
  protected function appendVar($key, $value) {
    if (!is_array($this->vars[$key])) {
      if (isset($this->vars[$key])) {
        throw new ThemeException('Non array to array conversion at: ' . $key);
      }
      $this->vars[$key] = array();
    }
    $this->vars[$key][] = $value;
  }

  /**
   * @param string $key
   * @return bool
   */
  protected function hasVar($key) {
    return isset($this->vars[$key]);
  }

  /**
   * @return mixed
   */
  abstract public function execute();

  /**
   * @return bool
   */
  abstract public function isApplicable();
}
