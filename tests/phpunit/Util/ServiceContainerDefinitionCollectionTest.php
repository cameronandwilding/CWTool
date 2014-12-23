<?php
/**
 * @file
 */

use CW\Util\ServiceContainerDefinitionCollection;

require_once __DIR__ . '/../../../vendor/autoload.php';

class ServiceContainerDefinitionCollectionTest extends PHPUnit_Framework_TestCase {

  public function testBasic() {
    $collection = new ServiceContainerDefinitionCollection();
    $this->assertEmpty($collection->getConfigs());

    $configsRaw = array();

    $count = 10;
    for ($i = 1; $i <= $count; $i++) {
      $value = md5(microtime(TRUE));
      $configsRaw[] = $value;

      $collection->add($value);
      $this->assertEquals($i, count($collection->getConfigs()));
    }

    $collectionItems = $collection->getConfigs();
    for ($i = 0; $i < $count; $i++) {
      $this->assertTrue(in_array($configsRaw[$i], $collectionItems));
    }
  }

}
