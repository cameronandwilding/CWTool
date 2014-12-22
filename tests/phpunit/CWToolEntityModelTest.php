<?php
/**
 * @file
 *
 * Entity container test.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Class CWToolEntityModelTest
 */
class CWToolEntityModelTest extends PHPUnit_Framework_TestCase {
  protected $entityType;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectLoader;

  /**
   * @var \CW\Util\LocalProcessIdentityMap
   */
  protected $localProcessIdentityMap;

  /**
   * @var \CW\Controller\EntityControllerFactory
   */
  protected $entityControllerFactory;

  public function setUp() {
    parent::setUp();

    $this->entityType = md5(microtime(TRUE));
    $this->localProcessIdentityMap = new \CW\Util\LocalProcessIdentityMap();
    $this->objectLoader = $this->getMock('CW\Model\DrupalObjectLoader');
    $this->entityControllerFactory = new \CW\Controller\EntityControllerFactory(
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

}
