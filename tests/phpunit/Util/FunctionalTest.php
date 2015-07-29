<?php
/**
 * @file
 */
use CW\Test\TestCase;
use CW\Util\Functional;

/**
 * Class FunctionalTest
 */
class FunctionalTest extends TestCase {

  /**
   * Test memoization.
   */
  public function testMemoize() {
    $f = function () {
      static $count = 0;

      $count++;

      return $count;
    };

    $this->assertEquals($f(), 1);
    $this->assertEquals($f(), 2);
    $this->assertEquals($f(), 3);

    $f_cached = Functional::memoize($f);
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
  }

  public function testApply() {
    $tenTimes = function (&$x) {
      return $x * 10;
    };
    $arr = $arrOrig = [1, 2, 3];
    Functional::apply($arr, $tenTimes);

    foreach ($arrOrig as $idx => $elem) {
      $this->assertEquals($tenTimes($elem), $arr[$idx]);
    }
  }

}
