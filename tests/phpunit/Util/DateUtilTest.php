<?php
/**
 * @file
 */

use CW\Util\DateUtil;

require_once __DIR__ . '/../../../vendor/autoload.php';

class DateUtilTest extends PHPUnit_Framework_TestCase {

  public function testFieldExtraction() {
    $date = '2014-02-20';
    $fieldDate = [
      'value' => $date,
      'timezone' => 'GMT',
    ];

    $ts = DateUtil::getTimestampFromISODateFieldValue($fieldDate);
    $this->assertNotNull($ts);
    $this->assertEquals(strtotime($date), $ts);
  }

  public function testDaySeconds() {
    $this->assertEquals(DateUtil::dayInSeconds(0), 0);
    $this->assertEquals(DateUtil::dayInSeconds(1), 86400);
  }

}
