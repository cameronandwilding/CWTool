<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\ControllerContainer;
use CW\Test\TestCase;

class ControllerContainerTest extends TestCase {

  private $loggerMock;

  private $objectHandlerMock;

  private $entityType;

  private $entityId;

  private $ctrlMock;

  public function setUp() {
    parent::setUp();

    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalEntityHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = self::randomString();
    $this->entityId = self::randomInt();

    /** @var \PHPUnit_Framework_MockObject_MockObject $ctrlMock */
    $this->ctrlMock = $this->getMock('CW\Controller\AbstractEntityController', [], [
      $this->loggerMock,
      $this->entityType,
      $this->entityId
    ]);
  }

  public function testContainerViaConstructor() {
    $container = new TestControllerContainer($this->ctrlMock);
    $this->assertEquals($this->ctrlMock, $container->getController());
  }

  public function testContainerViaFactory() {
    $container = TestControllerContainer::factory($this->ctrlMock);
    $this->assertEquals($this->ctrlMock, $container->getController());
  }

}

class TestControllerContainer extends ControllerContainer { }
