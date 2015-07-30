<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\FileController;
use CW\Test\TestCase;

class FileControllerTest extends TestCase {

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  /**
   * @var FileController
   */
  protected $controller;

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var int
   */
  protected $entityId;

  /**
   * @var object
   */
  private $fullEntity;

  public function testClassProperties() {
    $this->assertEquals(FileController::getClassEntityType(), FileController::ENTITY_TYPE);
  }

  public function setUp() {
    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalEntityHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = self::randomString();
    $this->entityId = self::randomInt();
    $this->controller = new FileController($this->loggerMock, $this->objectHandlerMock, $this->entityType, $this->entityId);

    $this->fullEntity = (object) [
      'filename' => 'foobar.png',
      'uri' => 'public://foobar.png',
    ];
  }

  public function testFilename() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('foobar.png', $this->controller->getFileName());
  }

  public function testURI() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('public://foobar.png', $this->controller->getFileURI());
  }

}
