<?php
/**
 * @file
 */

/**
 * Class FunctionalTest
 */
class FunctionalTest extends \CW\Test\TestCase {

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

    $f_cached = \CW\Util\Functional::memoize($f);
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
  }

}
