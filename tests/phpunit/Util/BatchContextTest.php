<?php
/**
 * @file
 */

use CW\Test\TestCase;
use CW\Util\BatchContext;

class BatchContextTest extends TestCase {

  public $contextArray = [];

  /**
   * @var BatchContext
   */
  public $contextObject;

  public function setUp() {
    $this->contextObject = new BatchContext($this->contextArray);
  }

  public function testFinished() {
    $this->assertEquals(0, $this->contextObject->getFinished());
    $this->assertTrue(empty($this->contextArray['finished']));
    $this->assertFalse($this->contextObject->isFinished());

    $this->contextObject->setFinished(0.5);
    $this->assertEquals(0.5, $this->contextObject->getFinished());
    $this->assertEquals(0.5, $this->contextArray['finished']);
    $this->assertFalse($this->contextObject->isFinished());

    $this->contextObject->setFinished(1);
    $this->assertEquals(1, $this->contextObject->getFinished());
    $this->assertEquals(1, $this->contextArray['finished']);
    $this->assertTrue($this->contextObject->isFinished());

    $this->contextObject->setFinished(1.0);
    $this->assertEquals(1, $this->contextObject->getFinished());
    $this->assertEquals(1, $this->contextArray['finished']);
    $this->assertTrue($this->contextObject->isFinished());

    $this->contextObject->setFinished(1.5);
    $this->assertEquals(1.5, $this->contextObject->getFinished());
    $this->assertEquals(1.5, $this->contextArray['finished']);
    $this->assertTrue($this->contextObject->isFinished());
  }

  public function testFinishedComplete() {
    $this->assertEquals(0, $this->contextObject->getFinished());
    $this->assertTrue(empty($this->contextArray['finished']));
    $this->assertFalse($this->contextObject->isFinished());

    $this->contextObject->setFinishedComplete();
    $this->assertTrue($this->contextObject->isFinished());
  }

  public function testInternals() {
    $this->assertEquals(NULL, $this->contextObject->foo);

    $this->contextObject->foo = 123;
    $this->assertEquals(123, $this->contextObject->foo);
    $this->assertEquals(123, $this->contextArray['sandbox']['foo']);
  }

  public function testFirstRun() {
    $this->assertTrue($this->contextObject->isFirstRun());
    $this->assertFalse($this->contextObject->isFirstRun());
    $this->assertFalse($this->contextObject->isFirstRun());
  }

  public function testRefs() {
    $this->contextObject->arr = ['abc'];
    $this->assertEquals(['abc'], $this->contextObject->arr);

    $arrVar = $this->contextObject->arr;
    $arrVar[] = 'gih';
    $this->assertEquals(['abc'], $this->contextObject->arr);

    $arrRef = &$this->contextObject->arr;
    $arrRef[] = 'def';
    $this->assertEquals(['abc', 'def'], $this->contextObject->arr);
  }

}
