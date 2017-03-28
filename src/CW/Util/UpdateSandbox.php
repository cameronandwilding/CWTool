<?php

namespace CW\Util;

/**
 * Class UpdateSandbox
 * @package CW\Util
 *
 * Represents the sandbox variable passed in the update hook.
 */
class UpdateSandbox {

  const KEY_FIRST_RUN_DONE = 'first_run';
  const KEY_FINISHED = '#finished';

  private $sandbox;

  public function __construct(&$sandbox) {
    $this->sandbox = &$sandbox;
  }

  public function isFirstRun() {
    $isFirstRun = !@$this->sandbox[self::KEY_FIRST_RUN_DONE];
    $this->sandbox[self::KEY_FIRST_RUN_DONE] = TRUE;
    return $isFirstRun;
  }

  public function &__get($name) {
    if (!isset($this->sandbox[$name])) {
      $this->sandbox[$name] = NULL;
    }
    return $this->sandbox[$name];
  }

  public function __set($name, $value) {
    $this->sandbox[$name] = $value;
  }

  public function __unset($name) {
    unset($this->sandbox[$name]);
  }

  public function setProgressPercentage($val) {
    $this->sandbox[self::KEY_FINISHED] = $val;
  }

  public function setProgress($done, $total) {
    $val = $total == 0 ? 0 : $done / $total;
    $this->sandbox[self::KEY_FINISHED] = $val;
  }

  public function setCompleted() {
    $this->sandbox[self::KEY_FINISHED] = 1;
  }

}
