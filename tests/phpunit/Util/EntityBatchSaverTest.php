<?php
/**
 * @file
 *
 * Entity batch saver test.
 */

use CW\Model\EntityModel;
use CW\Util\EntityBatchSaver;
use CW\Util\LocalProcessIdentityMap;

require_once __DIR__ . '/../../../vendor/autoload.php';

class EntityBatchSaverTest extends PHPUnit_Framework_TestCase {

  protected $objectLoaderMock;

  protected $entityBatchSaver;

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

    $entityType = md5(microtime(TRUE));
    $entityId1 = rand(1, 1000);
    $entityId2 = rand(1, 1000);

    $this->objectLoaderMock = $this->getMock('CW\\Model\\ObjectLoader');
    $this->entities[0] = new EntityModel($this->objectLoaderMock, $entityType, $entityId1);
    $this->entities[1] = new EntityModel($this->objectLoaderMock, $entityType, $entityId2);

    $identityMap->add($entityId1, $this->entities[0]);
    $identityMap->add($entityId2, $this->entities[1]);
  }

  public function testBatchSaveNothing() {
    $this->objectLoaderMock
      ->expects($this->exactly(0))
      ->method('save');

    $this->entityBatchSaver->saveAll();
  }

  public function testBatchSaveOne() {
    $this->objectLoaderMock
      ->expects($this->exactly(1))
      ->method('save');

    $this->entities[0]->setDirty();

    $this->entityBatchSaver->saveAll();
  }

  public function testBatchSaveMultiple() {
    $this->objectLoaderMock
      ->expects($this->exactly(2))
      ->method('save');

    $this->entities[0]->setDirty();
    $this->entities[1]->setDirty();

    $this->entityBatchSaver->saveAll();
  }

}
