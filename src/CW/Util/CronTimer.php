<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\VariableAdapter;

class CronTimer {

  const CACHE_PREFIX = 'cw_cron_last_run_';

  // 5 min.
  const DEFAULT_INTERVAL_SECONDS = 300;

  /**
   * @var \CW\Adapter\VariableAdapter
   */
  private $variableAdapter;

  public function __construct(VariableAdapter $variableAdapter) {
    $this->variableAdapter = $variableAdapter;
  }

  public function isTimePassedSinceLastRun($cronKey, $intervalSeconds = self::DEFAULT_INTERVAL_SECONDS) {
    // @todo think about a cached database storage implementation instead of variables
    $lastRun = $this->variableAdapter->get($this->getCacheKey($cronKey), 0);
    // @todo think about injecting Request object and have an ITimeable interface
    return $lastRun + $intervalSeconds < Request::current()->getTimestamp();
  }

  public function registerRun($cronKey) {
    $this->variableAdapter->set($this->getCacheKey($cronKey), Request::current()->getTimestamp());
  }

  protected function getCacheKey($cronKey) {
    return self::CACHE_PREFIX . $cronKey;
  }

}
