<?php
/**
 * @file
 */

use CW\Util\SimpleList;

class ServiceContainerDefinitionCollectionTest extends PHPUnit_Framework_TestCase {

  public function testBasic() {
    $collection = new SimpleList();
    $this->assertEmpty($collection->getAll());

    $configsRaw = array();

    $count = 10;
    for ($i = 1; $i <= $count; $i++) {
      $value = md5(microtime(TRUE));
      $configsRaw[] = $value;

      $collection->add($value);
      $this->assertEquals($i, count($collection->getAll()));
    }

    $collectionItems = $collection->getAll();
    for ($i = 0; $i < $count; $i++) {
      $this->assertTrue(in_array($configsRaw[$i], $collectionItems));
    }
  }

}
