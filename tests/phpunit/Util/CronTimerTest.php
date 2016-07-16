<?php
/**
 * @file
 */

class CronTimerTest extends CW\Test\TestCase {

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $variableAdapterMock;

  /**
   * @var \CW\Util\CronTimer
   */
  protected $cronTimer;

  public function setUp() {
    $this->variableAdapterMock = $this->getMockBuilder('CW\Adapter\VariableAdapter')->getMock();
    $this->cronTimer = new \CW\Util\CronTimer($this->variableAdapterMock);
  }

  public function testCronShouldRunFirstAlways() {
    $this->variableAdapterMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(0);

    $shouldRun = $this->cronTimer->isTimePassedSinceLastRun('foo', 10);
    $this->assertTrue($shouldRun);
  }

  public function testCronShouldRunAfterExpiration() {
    $this->variableAdapterMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(time() - 20);

    $shouldRun = $this->cronTimer->isTimePassedSinceLastRun('foo', 10);
    $this->assertTrue($shouldRun);
  }

  public function testCronShouldNotRunBeforeExpiration() {
    $this->variableAdapterMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(time() - 5);

    $shouldRun = $this->cronTimer->isTimePassedSinceLastRun('foo', 10);
    $this->assertFalse($shouldRun);
  }

  public function testCronRegisteringEvent() {
    $this->variableAdapterMock
      ->expects($this->once())
      ->method('set')
      ->with('cw_cron_last_run_foo');

    $this->cronTimer->registerRun('foo');
  }

}
