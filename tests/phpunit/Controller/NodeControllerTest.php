<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\NodeController;
use CW\Test\TestCase;

// Assist on Drupal defines for test.
if (!defined('LANGUAGE_NONE')) { define('LANGUAGE_NONE', 'und'); }
if (!defined('NODE_PUBLISHED')) { define('NODE_PUBLISHED', 1); }

class NodeControllerTest extends TestCase {

  const LANGUAGE_NONE = 'und';

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var NodeController
   */
  protected $controller;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  protected $entityType;

  protected $entityId;

  private $fullEntity;

  public function setUp() {
    parent::setUp();

    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalEntityHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = self::randomString();
    $this->entityId = self::randomInt();
    NodeController::setObjectHandler($this->objectHandlerMock);
    $this->controller = new NodeController($this->loggerMock, $this->entityType, $this->entityId);

    $this->fullEntity = (object) [
      'status' => 1,
      'title' => 'foobar',
      'created' => 123456789,
    ];
  }

  public function testClassType() {
    $this->assertEquals(NodeController::TYPE_NODE, NodeController::getClassEntityType());
  }

  public function testClassBundle() {
    $this->setExpectedException('CW\Exception\MissingImplementationException');
    NodeController::getClassEntityBundle();
  }

  public function testStatus() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertTrue($this->controller->isPublished());

    $this->fullEntity->status = FALSE;
    $this->assertFalse($this->controller->isPublished());

    $this->fullEntity->status = 0;
    $this->assertFalse($this->controller->isPublished());

    $this->fullEntity->status = NULL;
    $this->assertFalse($this->controller->isPublished());

    unset($this->fullEntity->status);
    $this->assertFalse($this->controller->isPublished());
  }

  public function testPath() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('loadSingleEntity');

    $this->assertEquals('node/' . $this->entityId, $this->controller->getPath());
  }

  public function testTitle() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('foobar', $this->controller->getTitle());

    unset($this->fullEntity->title);
    $this->assertEquals(NULL, $this->controller->getTitle());
  }

  public function testCreated() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals(123456789, $this->controller->getCreatedTimestamp());

    unset($this->fullEntity->created);
    $this->assertEquals(NULL, $this->controller->getCreatedTimestamp());
  }
  
}
