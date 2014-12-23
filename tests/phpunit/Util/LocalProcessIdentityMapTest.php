<?php
/**
 * @file
 */

use CW\Util\LocalProcessIdentityMap;

require_once __DIR__ . '/../../../vendor/autoload.php';

class LocalProcessIdentityMapTest extends PHPUnit_Framework_TestCase {

  public function testBasic() {
    $idmap = new LocalProcessIdentityMap();

    $key = md5(microtime(TRUE));

    $this->assertFalse($idmap->keyExist($key), 'key does not exist');
    $this->assertEquals(count($idmap->getAllItems()), 0, 'no items');

    $value = new stdClass();
    $idmap->add($key, $value);

    $this->assertEquals(
      $value,
      $idmap->get($key),
      'item is the same'
    );
    $this->assertTrue($idmap->keyExist($key), 'key exist');
    $this->assertEquals(1, count($idmap->getAllItems()), 'map has one item');

    $idmap->delete($key);
    $this->assertFalse($idmap->keyExist($key), 'key does not exist');
    $this->assertEquals(count($idmap->getAllItems()), 0, 'no items');
  }

  public function testExceptionWhenDoesNotExist() {
    $idmap = new LocalProcessIdentityMap();

    $this->setExpectedException('CW\Exception\IdentityMapException');

    $key = md5(microtime(TRUE));
    $idmap->get($key);
  }

  public function testExceptionOnSameItemAdd() {
    $idmap = new LocalProcessIdentityMap();
    $key = md5(microtime(TRUE));

    $idmap->add($key, 'foobar');

    $this->setExpectedException('CW\Exception\IdentityMapException');

    $idmap->add($key, 'foobar');
  }

  public function testDeleteAll() {
    $idmap = new LocalProcessIdentityMap();

    $length = 10;
    for ($i = 0; $i < $length; $i++) {
      $key = md5(microtime(TRUE) + rand(1, 1000000));
      $value = microtime(TRUE);
      $idmap->add($key, $value);
      $this->assertEquals($i + 1, count($idmap->getAllItems()));
      $this->assertTrue($idmap->keyExist($key));
    }

    $this->assertEquals(
      $length,
      count($idmap->getAllItems())
    );

    $idmap->deleteAll();
    $this->assertEquals(
      0,
      count($idmap->getAllItems())
    );
  }

}
