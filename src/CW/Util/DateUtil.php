<?php
/**
 * @file
 *
 * Date utilities.
 */

namespace CW\Util;

use DateTime;
use DateTimeZone;

/**
 * Class DateUtil
 * @package CW\Util
 */
class DateUtil {

  // One day in seconds.
  const DAY_IN_SECONDS = 86400;

  /**
   * Extracts the timestamp (using timezone) from ISO date fields.
   *
   * @param array $value
   *  Entity field value fetched via field_get_items().
   * @return int
   */
  public static function getTimestampFromISODateFieldValue($value) {
    $dateRaw = $value['value'];
    $date = new DateTime($dateRaw, new DateTimeZone($value['timezone']));
    return $date->getTimestamp();
  }

  /**
   * @param int $n
   * @return int
   */
  public static function dayInSeconds($n) {
    return $n * self::DAY_IN_SECONDS;
  }

  /**
   * @param int $timestamp
   * @return int
   */
  public static function firstSecondOfDayInTimestamp($timestamp) {
    return strtotime(date('Y-m-d', $timestamp));
  }

  /**
   * Returns a nice date format using range detection, such as:
   * 3 -> 3 seconds,
   * 130 -> 2 minutes,
   * 1900800 -> 3 weeks.
   *
   * @param int $seconds
   *  Quantity, not the timestamp.
   * @return array
   *  [int, string] - quantity and unit name (eg, [3, weeks]).
   */
  public static function formatSecondsWithDynamicRanges($seconds) {
    $seconds = abs($seconds);

    $ranges = array(
      array('seconds', 1, 90),
      array('minutes', 60, 5400),
      array('hours', 3600, 129600),
      array('days', 86400, 907200),
      array('weeks', 604800, 3888000),
      array('months', 2592000, 30758400),
      array('years', 30758400, PHP_INT_MAX),
    );

    foreach ($ranges as $range) {
      list($name, $divider, $limit) = $range;
      if ($seconds <= $limit) {
        return array(round($seconds / $divider), $name);
      }
    }

    return array($seconds, 'seconds');
  }

}
