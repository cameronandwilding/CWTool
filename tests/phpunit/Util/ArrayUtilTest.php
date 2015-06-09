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
    ];

    \CW\Util\ArrayUtil::mergeCollection($original, $additions);

    $this->assertTrue(in_array('foo', $original));
    $this->assertTrue(in_array('bar', $original));
    $this->assertTrue(in_array('alfa', $original));
    $this->assertTrue(in_array('bravo', $original));
    $this->assertTrue(in_array('charlie', $original));
    $this->assertTrue(in_array('delta', $original));
  }

  public function testKeyedCollectionMerge() {
    $original = [
      'foo' => 'bar',
      'zoom' => 'zip',
    ];

    $additions = [
      [
        'alfa' => 'bravo',
      ],
      [
        'foo' => 'charlie',
      ],
    ];

    \CW\Util\ArrayUtil::mergeCollection($original, $additions);
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($original, 'alfa'), 'bravo');
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($original, 'foo'), 'charlie');
    $this->assertEquals(\CW\Util\ArrayUtil::mapTranslate($original, 'zoom'), 'zip');
  }
}
