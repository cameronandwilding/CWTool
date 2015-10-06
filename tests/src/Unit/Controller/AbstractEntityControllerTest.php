<?php
/**
 * @file
 */

namespace Drupal\Tests\cw_tool\Unit\Controller;

use Drupal\Tests\UnitTestCase;

/**
 * Class AbstractEntityControllerTest
 * @package Drupal\Tests\cw_tool\Unit\Controller
 *
 * @coversDefaultClass \Drupal\cw_tool\Controller\AbstractEntityController
 * @group cw_tool
 */
class AbstractEntityControllerTest extends UnitTestCase {

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  private $entityManagerMock;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  private $entityStorageMock;

  public function setUp() {
    $this->entityManagerMock = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->entityStorageMock = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    parent::setUp();
  }

  public function testGetEntityMainProperties() {
    $type = 'foobar';
    $id = 'baz';
    $ctrl = new MinimalEntityController($this->entityManagerMock, $type, $id);

    $this->assertEquals($type, $ctrl->getEntityType());
    $this->assertEquals($id, $ctrl->getEntityID());
  }
//
//  public function testGetEntity() {
//    $type = 'foobar';
//
//    $this->entityManagerMock
//      ->expects($this->once())
//      ->method('getStorage')
//      ->with($type)
//      ->willReturn($this->entityStorageMock);
//
//    $fakeEntity = (object) [
//    ];
//
//    $id = 'baz';
//
//    $this->entityStorageMock
//      ->expects($this->once())
//      ->method('load')
//      ->with($id)
//      ->willReturn($fakeEntity);
//
//    $ctrl = new MinimalEntityController($this->entityManagerMock, $type, $id);
//
//    $this->assertEquals($fakeEntity, $ctrl->getEntity());
//  }

}

class MinimalEntityController extends \Drupal\cw_tool\Controller\AbstractEntityController {

}