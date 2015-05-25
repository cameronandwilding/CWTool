<?php
/**
 * @file
 */

use CW\Controller\AbstractEntityController;
use CW\Controller\NodeController;
use CW\Test\TestCase;

/**
 * Class AbstractEntityControllerTest
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
    $this->controller = new TestController($this->objectHandlerMock, $this->loggerMock, $this->entityType, $this->entityId);
  }

  public function testLoadEntity() {
    $entity = (object) [
      'type' => self::randomString(),
      'id' => self::randomInt(),
    ];
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($entity);
    $entityLoaded = $this->controller->entity();
    // Second load to test if it's called once only.
    $this->controller->entity();
    $this->assertEquals($entity, $entityLoaded);
  }

  public function testEntityParams() {
    $this->assertEquals($this->entityId, $this->controller->getEntityId());
    $this->assertEquals($this->entityType, $this->controller->getEntityType());
  }

  public function testEntitySave() {
    $this->objectHandlerMock->expects($this->once())->method('save');
    $this->objectHandlerMock->expects($this->once())->method('loadSingleEntity');
    $this->controller->save();
  }

  public function testEntityDelete() {
    $this->objectHandlerMock->expects($this->once())->method('delete');
    $this->objectHandlerMock->expects($this->never())->method('save');
    $this->objectHandlerMock->expects($this->never())->method('loadSingleEntity');
    $this->controller->delete();
  }

  public function testLoadEntityMetadata() {
    $metadata = (object) [
      'foo' => self::randomString(),
      'bar' => self::randomInt(),
    ];
    $this->objectHandlerMock->expects($this->once())->method('loadSingleEntity');
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadMetadata')
      ->willReturn($metadata);
    $metadataLoaded = $this->controller->metadata();
    // Second load to test if it's called once only.
    $this->controller->metadata();
    $this->assertEquals($metadata, $metadataLoaded);
  }

  public function testStringOutput() {
    $string_from_cast = (string) $this->controller;
    $string_from_toString = $this->controller->__toString();
    $this->assertEquals($string_from_toString, $string_from_cast);
    $this->assertTrue(strpos($string_from_toString, get_class($this->controller)) !== FALSE);
  }

  public function testSettingEntityBeforeEntityLoad() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('loadSingleEntity');

    $entityFake = (object)['id' => self::randomInt()];
    $this->controller->setEntity($entityFake);

    $entityFakeLoad = $this->controller->entity();
    $this->assertEquals($entityFake, $entityFakeLoad);
  }

  public function testSettingEntityAfterEntityLoad() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity');

    $entityLoaded = $this->controller->entity();
    $entityFake = (object)['id' => self::randomInt()];

    $this->assertNotEquals($entityLoaded, $entityFake);

    $this->controller->setEntity($entityFake);

    $entityFakeLoad = $this->controller->entity();
    $this->assertEquals($entityFakeLoad, $entityFake);
  }

  public function testUnimplementedEntityType() {
    $this->setExpectedException('\Exception');
    TestController::getClassEntityType();
  }

  public function testUnimplementedEntityBundle() {
    $this->setExpectedException('\Exception');
    TestController::getClassEntityBundle();
  }

  public function testEntityValidityCheck() {

  }

}

class TestController extends AbstractEntityController { }

class TestValidityCheckController extends AbstractEntityController {

}
