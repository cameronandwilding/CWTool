<?php
/**
 * @file
 */

use CW\Controller\NodeController;
use CW\Test\TestCase;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * @file
 */

class AbstractEntityControllerTest extends TestCase {

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var NodeController
   */
  protected $controller;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  protected $entityType;

  protected $entityId;

  public function setUp() {
    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalObjectHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = self::randomString();
    $this->entityId = self::randomInt();
    $this->controller = new NodeController($this->objectHandlerMock, $this->loggerMock, $this->entityType, $this->entityId);
  }

//  public function testLoadModel() {
//    $this->assertEquals(
//      $this->entityModelMock,
//      $this->controller->getEntityModel()
//    );
//  }

//  public function testLoadDrupalData() {
//    $this->entityModelMock
//      ->expects($this->once())
//      ->method('getEntityData');
//    $this->controller->data();
//  }

//  public function testLoadMetaData() {
//    $this->entityModelMock
//      ->expects($this->once())
//      ->method('getEntityMetadataWrapper');
//    $this->controller->metadata();
//  }

  public function testStringOutput() {
    $string_from_cast = (string) $this->controller;
    $string_from_toString = $this->controller->__toString();
    $this->assertEquals($string_from_toString, $string_from_cast);
    $this->assertTrue(strpos($string_from_toString, get_class($this->controller)) !== FALSE);
  }

}
