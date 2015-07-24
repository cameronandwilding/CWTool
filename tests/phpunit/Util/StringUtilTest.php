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

}
