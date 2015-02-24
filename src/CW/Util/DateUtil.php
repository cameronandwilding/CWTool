<?php
/**
 * @file
 */

namespace CW\Util;

use DateTime;
use DateTimeZone;

class DateUtil {

  const DAY_IN_SECONDS = 86400;

  public static function getTimestampFromDateFieldValue($value) {
    $dateRaw = $value['value'];
    $date = new DateTime($dateRaw, new DateTimeZone($value['timezone']));
    return $date->getTimestamp();
  }

}
