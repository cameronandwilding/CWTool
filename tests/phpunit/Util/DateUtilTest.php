<?php
/**
 * @file
 */

use CW\Util\DateUtil;

class DateUtilTest extends \CW\Test\TestCase {

  /**
   * @todo: This test is unstable and will fail when your timezone is not GMT
   */
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
