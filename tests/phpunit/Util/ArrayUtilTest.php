<?php
/**
 * @file
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

class ArrayUtilTest extends PHPUnit_Framework_TestCase {

  public function testMapTranslate() {
    $map = [
      'zero',
      'foo' => 'bar',
      'bar' => 123
    ];

    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($map, 'foo'), 'bar');
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($map, 'bar'), 123);
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($map, 0), 'zero');

    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($map, 'error'), NULL);
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($map, 'error', 'miss'), 'miss');
  }

}
