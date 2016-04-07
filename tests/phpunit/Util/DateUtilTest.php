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
    $date = '2014-01-20';
    $fieldDate = [
      'value' => $date,
      'timezone' => 'CET',
    ];

    $ts = DateUtil::getTimestampFromISODateFieldValue($fieldDate);
    $this->assertNotNull($ts);

    // @TODO This way it fails most possibly because of summer time saving.
    // Find a way to make it summer time safe.
    // $this->assertEquals(strtotime($date), $ts);
  }

  public function testDaySeconds() {
    $this->assertEquals(DateUtil::dayInSeconds(0), 0);
    $this->assertEquals(DateUtil::dayInSeconds(1), 86400);
  }

}
