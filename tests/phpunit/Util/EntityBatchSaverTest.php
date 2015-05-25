<?php
/**
 * @file
 *
 * Entity batch saver test.
 */

use CW\Controller\AbstractEntityController;
use CW\Test\TestCase;
use CW\Util\EntityBatchSaver;
use CW\Util\LocalProcessIdentityMap;

class EntityBatchSaverTest extends TestCase {

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var EntityBatchSaver
   */
  protected $entityBatchSaver;

  /**
   * @var AbstractEntityController[]
   */
  protected $entities = array();

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  public function setUp() {
    parent::setUp();

    $identityMap = new LocalProcessIdentityMap();
    $this->logger = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityBatchSaver = new EntityBatchSaver($identityMap, $this->logger);

    $entityType = self::randomString();
    $entityId1 = self::randomInt();
    $entityId2 = self::randomInt();

    $this->objectHandlerMock = $this->getMock('CW\Model\EntityHandler');
    FakeEntityController::setObjectHandler($this->objectHandlerMock);
    $this->entities[0] = new FakeEntityController($this->logger, $entityType, $entityId1);
    $this->entities[1] = new FakeEntityController($this->logger, $entityType, $entityId2);

    $identityMap->add($entityId1, $this->entities[0]);
    $identityMap->add($entityId2, $this->entities[1]);
  }

  public function testBatchSaveNothing() {
    $this->objectHandlerMock
      ->expects($this->exactly(0))
      ->method('save');

    $this->entityBatchSaver->saveAll();
  }

  public function testBatchSaveOne() {
    $this->objectHandlerMock
      ->expects($this->exactly(1))
      ->method('save');

    $this->entities[0]->setDirty();

    $this->entityBatchSaver->saveAll();
  }

  public function testBatchSaveMultiple() {
    $this->objectHandlerMock
      ->expects($this->exactly(2))
      ->method('save');

    $this->entities[0]->setDirty();
    $this->entities[1]->setDirty();

    $this->entityBatchSaver->saveAll();
  }

}

class FakeEntityController extends AbstractEntityController { }
