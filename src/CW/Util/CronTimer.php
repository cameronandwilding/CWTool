<?php
/**
 * @file
 *
 * Cron timer.
 */

namespace CW\Util;

use CW\Adapter\VariableAdapter;

/**
 * Class CronTimer
 * @package CW\Util
 *
 * Cron timer is responsible for verifying if a cron task is due to run.
 */
class CronTimer {

  // Cache key prefix.
  const CACHE_PREFIX = 'cw_cron_last_run_';

  // Default interval of run frequency. 5 min.
  const DEFAULT_INTERVAL_SECONDS = 300;

  /**
   * @var \CW\Adapter\VariableAdapter
   */
  private $variableAdapter;

  /**
   * @param \CW\Adapter\VariableAdapter $variableAdapter
   */
  public function __construct(VariableAdapter $variableAdapter) {
    $this->variableAdapter = $variableAdapter;
  }

  /**
   * @param string $cronKey
   *  Unique key for the cron task.
   * @param int $intervalSeconds
   * @return bool
   */
  public function isTimePassedSinceLastRun($cronKey, $intervalSeconds = self::DEFAULT_INTERVAL_SECONDS) {
    // @todo think about a cached database storage implementation instead of variables
    $lastRun = $this->variableAdapter->get($this->getCacheKey($cronKey), 0);
    // @todo think about injecting Request object and have an ITimeable interface
    return $lastRun + $intervalSeconds < Request::current()->getTimestamp();
  }

  /**
   * @param string $cronKey
   */
  public function registerRun($cronKey) {
    $this->variableAdapter->set($this->getCacheKey($cronKey), Request::current()->getTimestamp());
  }

  /**
   * @param string $cronKey
   * @return int
   */
  public function getLastRunTimestamp($cronKey) {
    return $this->variableAdapter->get($this->getCacheKey($cronKey), 0);
  }

  /**
   * @param string $cronKey
   * @return string
   */
  protected function getCacheKey($cronKey) {
    return self::CACHE_PREFIX . $cronKey;
  }

}
