<?php

/**
 * @file
 * Contains \Drupal\serialization\Tests\Normalizer\TypedDataNormalizerTest
 */

namespace Drupal\serialization\Tests\Normalizer;

use Drupal\Tests\UnitTestCase;
use Drupal\serialization\Normalizer\TypedDataNormalizer;

/**
 * Tests the TypedDataNormalizer class.
 *
 * @see \Drupal\serialization\Normalizer\TypedDataNormalizer
 *
 * @group Drupal
 */
class TypedDataNormalizerTest extends UnitTestCase {

  /**
   * The TypedDataNormalizer instance.
   *
   * @var \Drupal\serialization\Normalizer\TypedDataNormalizer
   */
  protected $normalizer;

  /**
   * The mock typed data instance.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedData;

  public static function getInfo() {
    return array(
      'name' => 'TypedDataNormalizer',
      'description' => 'Tests the TypedDataNormalizer class.',
      'group' => 'Serialization',
    );
  }

  public function setUp() {
    $this->normalizer = new TypedDataNormalizer();
    $this->typedData = $this->getMock('Drupal\Core\TypedData\TypedDataInterface');
  }

  /**
   * Tests the supportsNormalization() method.
   */
  public function testSupportsNormalization() {
    $this->assertTrue($this->normalizer->supportsNormalization($this->typedData));
    // Also test that an object not implementing TypedDataInterface fails.
    $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
  }

  /**
   * Tests the normalize() method.
   */
  public function testNormalize() {
    $this->typedData->expects($this->once())
      ->method('getValue')
      ->will($this->returnValue('test'));

    $this->assertEquals('test', $this->normalizer->normalize($this->typedData));
  }

}
