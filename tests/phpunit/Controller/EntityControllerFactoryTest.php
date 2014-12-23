<?php
/**
 * @file
 *
 * Entity container test.
 */

use CW\Controller\EntityControllerFactory;
use CW\Util\LocalProcessIdentityMap;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Class CWToolEntityModelTest
 */
class EntityControllerFactoryTest extends PHPUnit_Framework_TestCase {
  protected $entityType;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectLoader;

  /**
   * @var LocalProcessIdentityMap
   */
  protected $localProcessIdentityMap;

  /**
   * @var EntityControllerFactory
   */
  protected $entityControllerFactory;

  public function setUp() {
    parent::setUp();

    $this->entityType = md5(microtime(TRUE));
    $this->localProcessIdentityMap = new LocalProcessIdentityMap();
    $this->objectLoader = $this->getMock('CW\Model\DrupalObjectLoader');
    $this->entityControllerFactory = new EntityControllerFactory(
      $this->localProcessIdentityMap,
      $this->objectLoader,
      'CW\Controller\BasicEntityController',
      'CW\Model\EntityModel',
      $this->entityType
    );
    $this->entityControllerFactory;
  }

  public function testEntityInstantiation() {
    $entityId = rand(1, 1000);
    $entity = (object) array();
    $this->objectLoader
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->with($this->equalTo($this->entityType), $this->equalTo($entityId))
      ->willReturn($entity);
    $this->objectLoader
      ->expects($this->once())
      ->method('loadMetadata')
      ->with($this->equalTo($this->entityType), $this->equalTo($entity))
      ->willReturn($entity);
    $this->objectLoader
      ->expects($this->once())
      ->method('save')
      ->with()
      ->willReturn($entity);

    $model = $this->entityControllerFactory->initWithId($entityId);
    $this->assertEquals($model->getEntityModel()->entityId, $entityId);
    $this->assertEquals($model->getEntityModel()->entityType, $this->entityType);

    $data = $model->data();
    $metadata = $model->metadata();
    $model->getEntityModel()->save();

    $dataReload = $model->data();
    $metadataReload = $model->metadata();
    $this->assertEquals($data, $dataReload);
    $this->assertEquals($metadata, $metadataReload);
  }

  public function testSameObjectInitialization() {
    $id = rand(1, PHP_INT_MAX);
    $result_a = $this->entityControllerFactory->initWithId($id);
    $result_b = $this->entityControllerFactory->initWithId($id);
    $this->assertEquals($result_a, $result_b);
  }

  public function testWithInvalidControllerClass() {
    $mapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $loaderMock = $this->getMock('CW\Model\DrupalObjectLoader');
    $entity_type = md5(microtime(TRUE));

    $this->setExpectedException('\InvalidArgumentException');

    new EntityControllerFactory($mapMock, $loaderMock, 'EntityControllerFactoryTest_FakeEntityController', 'CW\Model\EntityModel', $entity_type);
  }

  public function testWithInvalidModelClass() {
    $mapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $loaderMock = $this->getMock('CW\Model\DrupalObjectLoader');
    $entity_type = md5(microtime(TRUE));

    $this->setExpectedException('\InvalidArgumentException');

    new EntityControllerFactory($mapMock, $loaderMock, 'CW\Controller\BasicEntityController', 'EntityControllerFactoryTest_FakeEntityModel', $entity_type);
  }

}

class EntityControllerFactoryTest_FakeEntityController { }

class EntityControllerFactoryTest_FakeEntityModel { }
