<?php
use CW\Test\TestCase;
use CW\Util\StringUtil;

/**
 * @file
 */
class StringUtilTest extends TestCase {

  public function testPathEncoding() {
    $strings = [
      'abc' => 'abc',
      '  abc  def  ' => '_abc_def_',
      '1 foo' => '1_foo',
      'FOfo' => 'fofo',
    ];

    foreach ($strings as $from => $to) {
      $toEncoded = StringUtil::pathEncode($from);
      $this->assertEquals($toEncoded, $to);
    }
  }

}
