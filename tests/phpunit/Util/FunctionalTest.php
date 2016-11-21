<?php
/**
 * @file
 */
use CW\Test\TestCase;
use CW\Util\ArrayUtil;
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

  public function testFirstPass() {
    $list = [1, 2, 'foo', 4, 'bar', 6];
    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    });
    $this->assertEquals($string, 'foo');
  }

  public function testFirstNotPass() {
    $list = [1, 2, 3, 4, 5, 6];

    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    }, 'boo');
    $this->assertEquals($string, 'boo');

    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    });
    $this->assertEquals($string, NULL);
  }

  public function testDot() {
    $addOne = function ($val) { return $val + 1; };
    $double = function ($val) { return $val * 2; };

    $this->assertEquals(10, Functional::dot([], 10));
    $this->assertEquals(11, Functional::dot([$addOne], 10));
    $this->assertEquals(22, Functional::dot([$addOne, $double], 10));
    $this->assertEquals(21, Functional::dot([$double, $addOne], 10));
  }

  public function testTimes() {
    $count = 0;

    Functional::times(0, function () use (&$count) { $count += 1; });
    $this->assertEquals(0, $count);

    Functional::times(10, function () use (&$count) { $count += 1; });
    $this->assertEquals(10, $count);
  }

  public function testWalk() {
    $count = 0;
    Functional::walk([2, 10], function ($item) use (&$count) { $count += $item; });
    $this->assertEquals(12, $count);
  }

  public function testSelfCallFn() {
    $nums = ArrayUtil::range(-2, 2, function ($n) { return new FunctionalTest__SimpleClassDummy($n); });
    $this->assertEquals([
      -2 => -20,
      -1 => -10,
      0 => 0,
      1 => 10,
      2 => 20,
    ], array_map(Functional::selfCallFn('getMultiple', 10), $nums));
  }

  public function testIdentity() {
    $fnID = Functional::id();
    $this->assertEquals(0, $fnID(0));
    $this->assertEquals(1, $fnID(1));
    $this->assertEquals(123, $fnID(123));
    $this->assertEquals("", $fnID(""));
    $this->assertEquals("hello", $fnID("hello"));
    $this->assertEquals([1, 'foo' => 'bar'], $fnID([1, 'foo' => 'bar']));

    $o = (object) ['foo' => [1, 2, 3], 'bar' => 'baz'];
    $this->assertEquals($o, $fnID($o));
  }

}

class FunctionalTest__SimpleClassDummy {
  public $n;
  public function __construct($n) { $this->n = $n; }
  public function getMultiple($mul) { return $this->n * $mul; }
}
