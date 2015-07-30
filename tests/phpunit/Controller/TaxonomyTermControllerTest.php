<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\TaxonomyTermController;
use CW\Test\TestCase;

class TaxonomyTermControllerTest extends TestCase {

  const LANGUAGE_NONE = 'und';

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var TaxonomyTermController
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
    $this->controller = new TaxonomyTermController($this->loggerMock, $this->objectHandlerMock, $this->entityType, $this->entityId);

    $this->fullEntity = (object) [
      'tid' => $this->entityId,
      'name' => 'foobar',
      'description' => 'barfoo',
    ];
  }

  public function testGetName() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('foobar', $this->controller->getName());
  }

  public function testDescription() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('barfoo', $this->controller->getDescription());
  }

  public function testPath() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('loadSingleEntity');

    $this->assertEquals("taxonomy/term/{$this->controller->getEntityId()}", $this->controller->getPath());
  }

}
