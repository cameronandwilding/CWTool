<?php
use CW\Util\ArrayUtil;

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

    $this->assertEquals(ArrayUtil::mapTranslate($map, 'foo'), 'bar');
    $this->assertEquals(ArrayUtil::mapTranslate($map, 'bar'), 123);
    $this->assertEquals(ArrayUtil::mapTranslate($map, 0), 'zero');

    $this->assertEquals(ArrayUtil::mapTranslate($map, 'error'), NULL);
    $this->assertEquals(ArrayUtil::mapTranslate($map, 'error', 'miss'), 'miss');
  }

  public function testArrayMapWithIllegalKey() {
    $this->assertNull(ArrayUtil::mapTranslate([], new stdClass()));
    $this->assertNull(ArrayUtil::mapTranslate([1 => 1], 1.0));
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

    ArrayUtil::mergeCollection($original, $additions);

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

    ArrayUtil::mergeCollection($original, $additions);
    $this->assertEquals(ArrayUtil::mapTranslate($original, 'alfa'), 'bravo');
    $this->assertEquals(ArrayUtil::mapTranslate($original, 'foo'), 'charlie');
    $this->assertEquals(ArrayUtil::mapTranslate($original, 'zoom'), 'zip');
  }

  public function testMultiLineStringToArray() {
    $string = 'foo' . PHP_EOL . 'bar' . "\n" . 'alpha' . "\r\n" . 'bravo';
    $string_to_array = ArrayUtil::multiLineStringToArray($string);

    $this->assertEquals($string_to_array[0], 'foo');
    $this->assertEquals($string_to_array[1], 'bar');
    $this->assertEquals($string_to_array[2], 'alpha');
    $this->assertEquals($string_to_array[3], 'bravo');
  }

  public function testMultiLineStringToArrayAndTrimValues() {
    $string = 'foo
    bar
    alpha
    bravo';
    $string_to_array = ArrayUtil::multiLineStringToArrayAndTrimValues($string);

    $this->assertEquals($string_to_array[0], 'foo');
    $this->assertEquals($string_to_array[1], 'bar');
    $this->assertEquals($string_to_array[2], 'alpha');
    $this->assertEquals($string_to_array[3], 'bravo');
  }

  public function testFilterKeys() {
    $original = [
      'foo' => 1,
      'bar' => 2,
      'baz' => 3,
    ];
    $filter = ['foo', 'baz'];
    $filteredArray = ArrayUtil::filterKeys($original, $filter);
    $this->assertEquals([
      'foo' => 1,
      'baz' => 3,
    ], $filteredArray);
  }

  public function testInsertAfterKey() {
    $subjects = [
      [
        // Normal in-array case.
        'subject' => [
          3 => 'three',
          5 => 'five',
          9 => 'nine',
        ],
        'key' => 5,
        'newKey' => 10,
        'newValue' => 'ten',
        'result' => [
          3 => 'three',
          5 => 'five',
          10 => 'ten',
          9 => 'nine',
        ],
      ],
      [
        // Not found - append case.
        'subject' => [
          3 => 'three',
          5 => 'five',
          9 => 'nine',
        ],
        'key' => 123,
        'newKey' => 10,
        'newValue' => 'ten',
        'result' => [
          3 => 'three',
          5 => 'five',
          9 => 'nine',
          10 => 'ten',
        ],
      ]
    ];
    foreach ($subjects as $subject) {
      ArrayUtil::insertAfterKey(
        $subject['subject'],
        $subject['key'],
        $subject['newKey'],
        $subject['newValue']
      );
      $this->assertEquals($subject['subject'], $subject['result']);
    }
  }

  public function testInsertAfterCondition() {
    $subjects = [
      [
        // Regular in array insert.
        'subject' => [10, 11, 12, 13, 14],
        'condition' => function ($key, $value) { return $value === 12; },
        'value' => 123,
        'result' => [10, 11, 12, 123, 13, 14],
      ],
      [
        // No match found - append.
        'subject' => [10, 11, 12, 13, 14],
        'condition' => function ($key, $value) { return $value === 99; },
        'value' => 123,
        'result' => [10, 11, 12, 13, 14, 123],
      ],
      [
        // Key match - multiple matches - insert after the first one.
        'subject' => [10, 11, 12, 13, 14],
        'condition' => function ($key, $value) { return $key >= 1; },
        'value' => 123,
        'result' => [10, 11, 123, 12, 13, 14],
      ],
    ];

    foreach ($subjects as $subject) {
      ArrayUtil::insertAfterCondition($subject['subject'], $subject['condition'], $subject['value']);
      $this->assertEquals($subject['subject'], $subject['result']);
    }
  }

}
