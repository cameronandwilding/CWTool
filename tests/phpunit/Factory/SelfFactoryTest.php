<?php
/**
 * @file
 */

use CW\Factory\SelfFactory;
use CW\Test\TestCase;

class SelfFactoryTest extends TestCase {

  public function testFactoryWithNoArgs() {
    $o = TestSelfFactoryEmptyConstructor::createInstance();
    $this->assertEquals('default', $o->var);
  }

  public function testFactoryWithArgs() {
    $o = TestSelfFactoryWithConstructorArgs::createInstance('boo');
    $this->assertEquals('boo', $o->var);
    $this->assertEquals('foobar', $o->untemperedVar);
  }

}

class TestSelfFactoryEmptyConstructor {
  use SelfFactory;

  public $var;

  public function __construct() {
    $this->var = 'default';
  }
}

class TestSelfFactoryWithConstructorArgs {
  use SelfFactory;

  public $var;

  public $untemperedVar;

  public function __construct($var, $untemperedVar = 'foobar') {
    $this->var = $var;
    $this->untemperedVar = $untemperedVar;
  }
}
