<?php
/**
 * @file
 *
 * Entity container test.
 */

use CW\Controller\AbstractEntityController;
use CW\Controller\EntityControllerFactory;
use CW\Test\TestCase;
use CW\Util\LocalProcessIdentityMap;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Class CWToolEntityModelTest
 */
class EntityControllerFactoryTest extends TestCase {

  protected $entityType;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var LocalProcessIdentityMap
   */
  protected $localProcessIdentityMap;

  /**
   * @var EntityControllerFactory
   */
  protected $nodeControllerFactory;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  public function setUp() {
    parent::setUp();

    $this->entityType = self::randomString();
    $this->localProcessIdentityMap = new LocalProcessIdentityMap();
    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalObjectHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->nodeControllerFactory = new EntityControllerFactory(
      $this->localProcessIdentityMap,
      $this->objectHandlerMock,
      'CW\Controller\NodeController',
      $this->entityType,
      $this->loggerMock
    );
    $this->nodeControllerFactory;
  }

  public function testEntityInstantiation() {
    $entityId = self::randomInt();
    $entity = (object) array();
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->with($this->equalTo($this->entityType), $this->equalTo($entityId))
      ->willReturn($entity);
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadMetadata')
      ->with($this->equalTo($this->entityType), $this->equalTo($entity))
      ->willReturn($entity);
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('save')
      ->with()
      ->willReturn($entity);

    $controller = $this->nodeControllerFactory->initWithId($entityId);
    $this->assertEquals($controller->getEntityId(), $entityId);
    $this->assertEquals($controller->getEntityType(), $this->entityType);

    $entityStored = $controller->entity();
    $metadata = $controller->metadata();
    $controller->save();

    $dataReload = $controller->entity();
    $metadataReload = $controller->metadata();
    $this->assertEquals($entityStored, $dataReload);
    $this->assertEquals($metadata, $metadataReload);
  }

  public function testSameObjectInitialization() {
    $id = self::randomInt();
    $result_a = $this->nodeControllerFactory->initWithId($id);
    $result_b = $this->nodeControllerFactory->initWithId($id);
    $this->assertEquals($result_a, $result_b);
  }

  public function testWithInvalidControllerClass() {
    $mapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $objectHandlerMock = $this->getMock('CW\Model\DrupalObjectHandler');
    $entity_type = self::randomString();

    $this->setExpectedException('\InvalidArgumentException');

    new EntityControllerFactory($mapMock, $objectHandlerMock, 'EntityControllerFactoryTest_FakeEntityController', $entity_type, $this->loggerMock);
  }

}

class EntityControllerFactoryTest_FakeEntityController { }

class EntityControllerFactoryTest_BasicEntityController extends AbstractEntityController { }
