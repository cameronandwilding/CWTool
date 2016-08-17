<?php
/**
 * @file
 */

namespace CW\Util;

class BatchContext {

  const KEY_SANDBOX = 'sandbox';
  const KEY_FIRST_RUN_DONE = 'first_run';
  const KEY_FINISHED = 'finished';

  const FINISHED_COMPLETE = 1;

  /**
   * @var array
   */
  private $context;

  public function __construct(&$context) {
    $this->context = &$context;
  }

  public function isFirstRun() {
    $isFirstRun = !@$this->context[self::KEY_SANDBOX][self::KEY_FIRST_RUN_DONE];
    $this->context[self::KEY_SANDBOX][self::KEY_FIRST_RUN_DONE] = TRUE;
    return $isFirstRun;
  }

  public function __get($name) {
    return @$this->context[self::KEY_SANDBOX][$name];
  }

  public function &getSandboxVarRef($name) {
    return $this->context[self::KEY_SANDBOX][$name];
  }

  public function __set($name, $value) {
    $this->context[self::KEY_SANDBOX][$name] = $value;
  }

  public function setFinished($finished) {
    $this->context[self::KEY_FINISHED] = $finished;
  }

  public function setFinishedComplete() {
    $this->setFinished(self::FINISHED_COMPLETE);
  }

  public function getFinished() {
    return $this->context[self::KEY_FINISHED];
  }

  public function isFinished() {
    return $this->context[self::KEY_FINISHED] >= self::FINISHED_COMPLETE;
  }

}
