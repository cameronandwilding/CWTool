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

}
