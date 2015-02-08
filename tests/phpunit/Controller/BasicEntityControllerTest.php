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
  protected $objectLoaderMock;

  /**
   * @var NodeController
   */
  protected $controller;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityModelMock;

  public function setUp() {
    $loaderMock = $this->getMock('CW\Model\DrupalObjectLoader');
    $this->entityModelMock = $this->getMock('CW\Model\EntityModel', [], [$loaderMock, 'fake type', 'fake id']);
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
