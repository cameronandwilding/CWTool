<?php
/**
 * @file
 */

namespace CW\Util;

class CronTimer {

  const CACHE_PREFIX = 'cw_cron_last_run_';

  // 5 min.
  const DEFAULT_INTERVAL_SECONDS = 300;

  public static function isTimePassedSinceLastRun($cronKey, $intervalSeconds = self::DEFAULT_INTERVAL_SECONDS) {
    // @todo think about a cached database storage implementation instead of variables
    $lastRun = variable_get(self::getCacheKey($cronKey), 0);
    // @todo think about injecting Request object and have an ITimeable interface
    return $lastRun + $intervalSeconds < Request::current()->getTimestamp();
  }

  public static function registerRun($cronKey) {
    variable_set(self::getCacheKey($cronKey), Request::current()->getTimestamp());
  }

  protected static function getCacheKey($cronKey) {
    return self::CACHE_PREFIX . $cronKey;
  }

}
