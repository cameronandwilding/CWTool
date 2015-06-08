<?php
/**
 * @file
 */

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

  public function testCollectionMerge() {
    $original = [
      'foo',
      'bar'
    ];

    $additions = [
      ['alfa', 'bravo', 'charlie'],
      ['delta'],
      ['echo', 'foxtrot'],
      ['golf', 'hotel']
    ];

    \CW\Util\ArrayUtil::mergeCollection($original, $additions);

    $this->assertTrue(in_array('foo', $original));
    $this->assertTrue(in_array('bar', $original));
    $this->assertTrue(in_array('alfa', $original));
    $this->assertTrue(in_array('bravo', $original));
    $this->assertTrue(in_array('charlie', $original));
    $this->assertTrue(in_array('delta', $original));
    $this->assertTrue(in_array('echo', $original));
    $this->assertTrue(in_array('foxtrot', $original));
    $this->assertTrue(in_array('golf', $original));
    $this->assertTrue(in_array('hotel', $original));
  }

}
