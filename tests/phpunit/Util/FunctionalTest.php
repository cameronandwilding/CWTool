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

  public function testAny() {
    $pass = [1, 2, 3, 4, 100];
    $noPass = [1, 2, 3, 4];

    $this->assertTrue(Functional::any($pass, function ($item) {return $item > 10;}));
    $this->assertFalse(Functional::any($noPass, function ($item) {return $item > 10;}));
  }

  public function testAll() {
    $pass = [1, 2, 3, 4, 100];
    $noPass = [1, 2, 3, 4];

    $this->assertFalse(Functional::all($pass, function ($item) {return $item < 10;}));
    $this->assertTrue(Functional::all($noPass, function ($item) {return $item < 10;}));
  }

}
