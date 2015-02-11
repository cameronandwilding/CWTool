<?php
use CW\Controller\NodeController;
use CW\Model\EntityModel;

/**
 * @file
 */

class BasicEntityControllerTest extends PHPUnit_Framework_TestCase {

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
  protected $entityModelMock;

  public function setUp() {
    $objectHandlerMock = $this->getMock('CW\Model\DrupalObjectHandler');
    $this->entityModelMock = $this->getMock('CW\Model\EntityModel', [], [$objectHandlerMock, 'fake type', 'fake id']);
    $this->controller = new NodeController($this->entityModelMock);
  }

  public function testLoadModel() {
    $this->assertEquals(
      $this->entityModelMock,
      $this->controller->getEntityModel()
    );
  }

  public function testLoadDrupalData() {
    $this->entityModelMock
      ->expects($this->once())
      ->method('getEntityData');
    $this->controller->data();
  }

  public function testLoadMetaData() {
    $this->entityModelMock
      ->expects($this->once())
      ->method('getEntityMetadataWrapper');
    $this->controller->metadata();
  }

}
