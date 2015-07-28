<?php
use CW\Test\TestCase;
use CW\Util\StringUtil;

/**
 * @file
 */
class StringUtilTest extends TestCase {

  public function testPathEncoding() {
    $strings = [
      'abc' => 'abc', // Same string.
      '  abc  def  ' => '_abc_def_', // Multiple illegal chars.
      '1 foo' => '1_foo', // Symbols.
      'FOfo' => 'fofo', // Upper case.
    ];

    foreach ($strings as $from => $to) {
      $toEncoded = StringUtil::pathEncode($from);
      $this->assertEquals($toEncoded, $to);
    }
  }

  public function testInsertTo() {
    $tests = [
      ['foo', 'bar', 0, 'barfoo'],
      ['foo', 'bar', 1, 'fbaroo'],
      ['”“foo', 'bar', 6, '”“barfoo'], // Multibyte warning.
      ['foo', 'bar', 3, 'foobar'],
      ['foo', 'bar', -1, 'fobaro'],
      ["foo\nfoo", 'bar', 3, "foobar\nfoo"], // New lines.
      ["foo\nfoo", 'bar', 4, "foo\nbarfoo"],
    ];

    foreach ($tests as $test) {
      $this->assertEquals($test[3], StringUtil::insertTo($test[0], $test[1], $test[2]));
    }
  }

  /**
   * @expectedException CW\Exception\CWException
   */
  public function testInsertToOutOfRange() {
    StringUtil::insertTo('foo', 'bar', 100);
  }

}
