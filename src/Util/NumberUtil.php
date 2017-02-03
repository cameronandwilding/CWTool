<?php
/**
 * @file
 *
 * Number helpers.
 */

namespace Drupal\cw_tool\Util;

/**
 * Class NumberUtil
 * @package Drupal\cw_tool\Util
 *
 * Number helpers.
 */
class NumberUtil {

  /**
   * @param float $total
   * @param float $partial
   * @return float
   */
  public static function percentage($total, $partial) {
    if ($total == 0 || $partial == 0) {
      return 0;
    }

    return ($partial / $total) * 100;
  }

}
