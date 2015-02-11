<?php
/**
 * Created by PhpStorm.
 * User: itarato
 * Date: 23/12/14
 * Time: 08:31
 */

use CW\Model\EntityModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

class EntityModelTest extends PHPUnit_Framework_TestCase {

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  protected $entityType;

  protected $entityId;

  /**
   * @var EntityModel
   */
  protected $entityModel;

  public function setUp() {
    $this->objectHandlerMock = $this->getMock('CW\Model\ObjectHandler');
    $this->entityType = md5(microtime(TRUE));
    $this->entityId = rand(1, PHP_INT_MAX);
    $this->entityModel = new EntityModel($this->objectHandlerMock, $this->entityType, $this->entityId);
  }

  public function testLoadModel() {
    $entityObjectFake = (object)['id' => $this->entityId, 'type' => $this->entityType, 'foo' => md5(microtime(TRUE))];

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->with($this->equalTo($this->entityType), $this->equalTo($this->entityId))
      ->willReturn($entityObjectFake);

    $this->objectHandlerMock->expects($this->never())->method('loadMetadata');
    $this->objectHandlerMock->expects($this->never())->method('loadMultipleEntity');
    $this->objectHandlerMock->expects($this->never())->method('save');

    $this->entityModel->getEntityData();
  }

  public function testLoadMetadata() {
    $entityObjectFake = (object)['id' => $this->entityId, 'type' => $this->entityType, 'foo' => md5(microtime(TRUE))];
    $entityMetadataFake = (object)['bar' => md5(microtime(TRUE))];

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->with($this->equalTo($this->entityType), $this->equalTo($this->entityId))
      ->willReturn($entityObjectFake);

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadMetadata')
      ->with($this->equalTo($this->entityType), $this->equalTo($entityObjectFake))
      ->willReturn($entityMetadataFake);

    $this->objectHandlerMock->expects($this->never())->method('loadMultipleEntity');
    $this->objectHandlerMock->expects($this->never())->method('save');

    $this->entityModel->getEntityMetadataWrapper();
  }

  public function testSave() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('save');

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity');

    $this->objectHandlerMock->expects($this->never())->method('loadMetadata');
    $this->objectHandlerMock->expects($this->never())->method('loadMultipleEntity');

    $this->assertFalse($this->entityModel->isDirty());
    $this->entityModel->setClean();
    $this->assertFalse($this->entityModel->isDirty());

    $this->entityModel->setDirty();
    $this->assertTrue($this->entityModel->isDirty());

    $this->entityModel->save();
    $this->assertFalse($this->entityModel->isDirty());
  }

  public function testManualEntityDataSet() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('loadSingleEntity');

    $entityDataFake = new stdClass();
    $this->entityModel->setDrupalEntityData($entityDataFake);

    $this->assertEquals($entityDataFake, $this->entityModel->getEntityData());
  }

}
