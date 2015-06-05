<?php
/**
 * @file
 */

use CW\Controller\AbstractEntityController;
use CW\Controller\NodeController;
use CW\Test\TestCase;
use CW\Util\FieldUtil;

// Assist on Drupal defines for test.
if (!defined('LANGUAGE_NONE')) {
  define('LANGUAGE_NONE', 'und');
}

/**
 * Class AbstractEntityControllerTest
 */
class AbstractEntityControllerTest extends TestCase {

  const LANGUAGE_NONE = 'und';

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var NodeController
   */
  protected $controller;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  protected $entityType;

  protected $entityId;

  /**
   * @var object
   */
  protected $fullEntity;

  public function setUp() {
    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalEntityHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = self::randomString();
    $this->entityId = self::randomInt();
    TestController::setObjectHandler($this->objectHandlerMock);
    $this->controller = new TestController($this->loggerMock, $this->entityType, $this->entityId);

    $this->fullEntity = (object) [
      'prop1' => 'val1',
      'propNull' => NULL,
      'propFalse' => FALSE,
      'field_one' => [
        self::LANGUAGE_NONE => [
          [
            FieldUtil::KEY_FILE_ID => FieldUtil::KEY_FILE_ID,
            FieldUtil::KEY_SAFE_VALUE => FieldUtil::KEY_SAFE_VALUE,
            FieldUtil::KEY_TARGET_ID => FieldUtil::KEY_TARGET_ID,
            FieldUtil::KEY_TAXONOMY_ID => FieldUtil::KEY_TAXONOMY_ID,
            FieldUtil::KEY_VALUE => FieldUtil::KEY_VALUE,
          ],
          [
            FieldUtil::KEY_FILE_ID => FieldUtil::KEY_FILE_ID,
            FieldUtil::KEY_SAFE_VALUE => FieldUtil::KEY_SAFE_VALUE,
            FieldUtil::KEY_TARGET_ID => FieldUtil::KEY_TARGET_ID,
            FieldUtil::KEY_TAXONOMY_ID => FieldUtil::KEY_TAXONOMY_ID,
            FieldUtil::KEY_VALUE => FieldUtil::KEY_VALUE,
          ],
        ],
      ],
      'field_empty' => [],
      'field_empty_lang' => [self::LANGUAGE_NONE => []],
      'field_null' => NULL,
    ];
  }

  public function testLoadEntity() {
    $entity = (object) [
      'type' => self::randomString(),
      'id' => self::randomInt(),
    ];
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($entity);
    $entityLoaded = $this->controller->entity();
    // Second load to test if it's called once only.
    $this->controller->entity();
    $this->assertEquals($entity, $entityLoaded);
  }

  public function testEntityParams() {
    $this->assertEquals($this->entityId, $this->controller->getEntityId());
    $this->assertEquals($this->entityType, $this->controller->getEntityType());
  }

  public function testEntitySave() {
    $this->objectHandlerMock->expects($this->once())->method('save');
    $this->objectHandlerMock->expects($this->once())->method('loadSingleEntity');
    $this->controller->save();
  }

  public function testEntityDelete() {
    $this->objectHandlerMock->expects($this->once())->method('delete');
    $this->objectHandlerMock->expects($this->never())->method('save');
    $this->objectHandlerMock->expects($this->never())->method('loadSingleEntity');
    $this->controller->delete();
  }

  public function testLoadEntityMetadata() {
    $metadata = (object) [
      'foo' => self::randomString(),
      'bar' => self::randomInt(),
    ];
    $this->objectHandlerMock->expects($this->once())->method('loadSingleEntity');
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadMetadata')
      ->willReturn($metadata);
    $metadataLoaded = $this->controller->metadata();
    // Second load to test if it's called once only.
    $this->controller->metadata();
    $this->assertEquals($metadata, $metadataLoaded);
  }

  public function testStringOutput() {
    $string_from_cast = (string) $this->controller;
    $string_from_toString = $this->controller->__toString();
    $this->assertEquals($string_from_toString, $string_from_cast);
    $this->assertTrue(strpos($string_from_toString, get_class($this->controller)) !== FALSE);
  }

  public function testSettingEntityBeforeEntityLoad() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('loadSingleEntity');

    $entityFake = (object)['id' => self::randomInt()];
    $this->controller->setEntity($entityFake);

    $entityFakeLoad = $this->controller->entity();
    $this->assertEquals($entityFake, $entityFakeLoad);
  }

  public function testSettingEntityAfterEntityLoad() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity');

    $entityLoaded = $this->controller->entity();
    $entityFake = (object)['id' => self::randomInt()];

    $this->assertNotEquals($entityLoaded, $entityFake);

    $this->controller->setEntity($entityFake);

    $entityFakeLoad = $this->controller->entity();
    $this->assertEquals($entityFakeLoad, $entityFake);
  }

  public function testUnimplementedEntityType() {
    $this->setExpectedException('\Exception');
    TestController::getClassEntityType();
  }

  public function testUnimplementedEntityBundle() {
    $this->setExpectedException('\Exception');
    TestController::getClassEntityBundle();
  }

  public function testEntityValidityCheckValid() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('extractIDs')
      ->willReturn(array($this->entityId, NULL, 'testbundle123'));


    $entity = new stdClass();
    TestWithBundleController::setObjectHandler($this->objectHandlerMock);
    $isValid = TestWithBundleController::isValidEntity($entity);

    $this->assertTrue($isValid);
  }

  /**
   * Entities with not matching bundle and not valid for the controller.
   */
  public function testEntityValidityCheckInvalidType() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('extractIDs')
      ->willReturn(array($this->entityId, NULL, 'testbundle123-wrong'));


    $entity = new stdClass();
    TestWithBundleController::setObjectHandler($this->objectHandlerMock);
    $isValid = TestWithBundleController::isValidEntity($entity);

    $this->assertFalse($isValid);
  }

  /**
   * Non object entities cannot be validated.
   */
  public function testEntityValidityCheckWithNonEntityObject() {
    $this->objectHandlerMock
      ->expects($this->never())
      ->method('extractIDs')
      ->willReturn(array($this->entityId, NULL, 'testbundle123'));


    TestWithBundleController::setObjectHandler($this->objectHandlerMock);

    $this->assertFalse(TestWithBundleController::isValidEntity(NULL));
    $this->assertFalse(TestWithBundleController::isValidEntity(''));
    $this->assertFalse(TestWithBundleController::isValidEntity([]));
    $this->assertFalse(TestWithBundleController::isValidEntity(1));
  }

  /**
   * Using an incomplete controller (missing bundle or type definition) will
   * no validate entity.
   */
  public function testEntityValidityCheckIncompleteController() {
    $entity = new stdClass();
    TestController::setObjectHandler($this->objectHandlerMock);
    $isValid = TestController::isValidEntity($entity);

    $this->assertFalse($isValid);
  }

  public function testEntityFieldValue() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one'));
    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE));
    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 0));
    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 1));
    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 0, self::LANGUAGE_NONE));
    $this->assertEquals(FieldUtil::KEY_VALUE, $this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 1, self::LANGUAGE_NONE));
  }

  public function testEntityFieldValueMissing() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertNull($this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 0, 'language_nonexisting'));
    $this->assertNull($this->controller->fieldValue('field_one', FieldUtil::KEY_VALUE, 2, self::LANGUAGE_NONE));
    $this->assertNull($this->controller->fieldValue('field_one', 'key_nonexisting', 0, self::LANGUAGE_NONE));

    $this->assertNull($this->controller->fieldValue('field_non_existing'));
    $this->assertNull($this->controller->fieldValue('field_empty'));
    $this->assertNull($this->controller->fieldValue('field_empty_lang'));
  }

  public function testEntityFieldTargetID() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals(FieldUtil::KEY_TARGET_ID, $this->controller->fieldTargetID('field_one'));
  }

  public function testEntityFieldFileID() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals(FieldUtil::KEY_FILE_ID, $this->controller->fieldFileID('field_one'));
  }

  public function testEntityMultiField() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $values = $this->controller->multiFieldValues('field_one');
    $values_with_key = $this->controller->multiFieldValues('field_one', FieldUtil::KEY_VALUE);
    $values_with_lang = $this->controller->multiFieldValues('field_one', FieldUtil::KEY_VALUE, self::LANGUAGE_NONE);

    $this->assertEquals($values, $values_with_key);
    $this->assertEquals($values, $values_with_lang);
    $this->assertEquals($values[0], FieldUtil::KEY_VALUE);
    $this->assertEquals($values[1], FieldUtil::KEY_VALUE);
    $this->assertEquals(count($values), 2);
  }

  public function testEntityMultiFieldNonExistingFieldKey() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $values = $this->controller->multiFieldValues('field_one', 'non-existing-key');

    $this->assertNull($values[0]);
    $this->assertNull($values[1]);
    $this->assertEquals(count($values), 2);
  }

  public function testEntityMultiFieldNonExistingField() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $values = $this->controller->multiFieldValues('field_non_existing_field');
    $this->assertEquals([], $values);
  }

  public function testEntityFieldItem() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $item = $this->controller->fieldItem('field_one');
    $item_with_idx = $this->controller->fieldItem('field_one', 0);
    $item_with_idx_second = $this->controller->fieldItem('field_one', 1);
    $item_with_lang = $this->controller->fieldItem('field_one', 0, self::LANGUAGE_NONE);
    $item_with_lang_second = $this->controller->fieldItem('field_one', 1, self::LANGUAGE_NONE);

    $this->assertEquals($item, $item_with_idx);
    $this->assertEquals($item, $item_with_lang);
    $this->assertEquals($item_with_idx_second, $item_with_lang_second);

    $this->assertEquals($this->fullEntity->field_one[self::LANGUAGE_NONE][0], $item);
    $this->assertEquals($this->fullEntity->field_one[self::LANGUAGE_NONE][1], $item_with_idx_second);
  }

  public function testEntityFieldItemMissing() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertNull($this->controller->fieldItem('field_non_existing', 0, self::LANGUAGE_NONE));
    $this->assertNull($this->controller->fieldItem('field_one', 2, self::LANGUAGE_NONE));
    $this->assertNull($this->controller->fieldItem('field_one', 0, 'lang-invalid'));
  }

  public function testEntityFieldItems() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $items = $this->controller->fieldItems('field_one');
    $items_with_lang = $this->controller->fieldItems('field_one', self::LANGUAGE_NONE);

    $this->assertEquals($items, $items_with_lang);
    $this->assertEquals($this->fullEntity->field_one[self::LANGUAGE_NONE], $items);
  }

  public function testEntityFieldItemsMissing() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals([], $this->controller->fieldItems('field_empty'));
    $this->assertEquals([], $this->controller->fieldItems('field_invalid', self::LANGUAGE_NONE));
    $this->assertEquals([], $this->controller->fieldItems('field_one', 'language_invalid'));
  }

  public function testEntityFieldReference() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'TestController',
      'foobar',
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->once())
      ->method('initWithId')
      ->with($this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TARGET_ID]);

    $this->controller->fieldReferencedEntityController('field_one', $factoryMock);
  }

  public function testEntityFieldReferenceMissing() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'TestController',
      'foobar',
      $this->loggerMock
    ]);

    $this->assertNull($this->controller->fieldReferencedEntityController('field_missing', $factoryMock));
  }

  public function testEntityFieldReferenceCustomKey() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'TestController',
      'foobar',
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->once())
      ->method('initWithId')
      ->with($this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TAXONOMY_ID]);

    $this->controller->fieldReferencedEntityController('field_one', $factoryMock, FieldUtil::KEY_TAXONOMY_ID);
  }

  public function testEntityFieldFileCtrlReference() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'CW\Controller\FileController',
      'file',
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->once())
      ->method('initWithId')
      ->with($this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_FILE_ID]);

    $this->controller->fieldReferencedFileCtrl('field_one', $factoryMock);
  }

  public function testEntityFieldTaxonomyTermCtrlReference() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'CW\Controller\TaxonomyTermController',
      \CW\Controller\TaxonomyTermController::getClassEntityType(),
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->once())
      ->method('initWithId')
      ->with($this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TAXONOMY_ID]);

    $this->controller->fieldReferencedTaxonomyTermCtrl('field_one', $factoryMock);
  }

  public function testEntityFieldValueSetUpdate() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $newVal = md5(microtime(TRUE));
    $this->controller->setFieldValue('field_one', $newVal);
    $this->assertEquals($newVal, $this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_VALUE]);

    $this->assertEquals(FieldUtil::KEY_TAXONOMY_ID, $this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TAXONOMY_ID]);
    $this->controller->setFieldValue('field_one', $newVal, FieldUtil::KEY_TAXONOMY_ID);
    $this->assertEquals($newVal, $this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TAXONOMY_ID]);
  }

  public function testEntityFieldValueSetNew() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertTrue(empty($this->fullEntity->field_one[self::LANGUAGE_NONE][0]['new_key']));
    $this->assertTrue(empty($this->fullEntity->field_new));

    $newVal = md5(microtime(TRUE));

    $this->controller->setFieldValue('field_one', $newVal, 'new_key');
    $this->assertEquals($newVal, $this->fullEntity->field_one[self::LANGUAGE_NONE][0]['new_key']);

    $this->controller->setFieldValue('field_new', $newVal);
    $this->assertEquals($newVal, $this->fullEntity->field_new[self::LANGUAGE_NONE][0][FieldUtil::KEY_VALUE]);
  }

  /**
   * Test the multi field update - field items are populated with the given values.
   * Verify that it's also erasing everything else.
   */
  public function testEntityFieldMultiValueSetUpdate() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $newVals = ['foo', 'bar'];
    $this->controller->setMultiFieldValues('field_one', $newVals);
    $this->assertEquals($newVals[0], $this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_VALUE]);
    $this->assertEquals($newVals[1], $this->fullEntity->field_one[self::LANGUAGE_NONE][1][FieldUtil::KEY_VALUE]);
    $this->assertEquals([FieldUtil::KEY_VALUE => $newVals[0]], $this->fullEntity->field_one[self::LANGUAGE_NONE][0]);
    $this->assertEquals([FieldUtil::KEY_VALUE => $newVals[1]], $this->fullEntity->field_one[self::LANGUAGE_NONE][1]);
  }

  public function testEntityFieldReferencesAll() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TARGET_ID] = 'foo';
    $this->fullEntity->field_one[self::LANGUAGE_NONE][1][FieldUtil::KEY_TARGET_ID] = 'bar';

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'TestController',
      'foobar',
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->exactly(2))
      ->method('initWithId')
      ->withConsecutive(
        [$this->fullEntity->field_one[self::LANGUAGE_NONE][0][FieldUtil::KEY_TARGET_ID]],
        [$this->fullEntity->field_one[self::LANGUAGE_NONE][1][FieldUtil::KEY_TARGET_ID]]
      );

    $this->controller->fieldAllReferencedEntityController('field_one', $factoryMock);
  }

  public function testEntityFieldReferencesAllMissing() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $localProcessIdentityMapMock = $this->getMock('CW\Util\LocalProcessIdentityMap');
    $factoryMock = $this->getMock('CW\Factory\EntityControllerFactory', [], [
      $localProcessIdentityMapMock,
      $this->objectHandlerMock,
      'TestController',
      'foobar',
      $this->loggerMock
    ]);
    $factoryMock
      ->expects($this->exactly(0))
      ->method('initWithId');

    $this->controller->fieldAllReferencedEntityController('field_non_existing', $factoryMock);
  }

  public function testEntityProperty() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('val1', $this->controller->property('prop1'));
    $this->assertEquals(NULL, $this->controller->property('propNull'));
    $this->assertEquals(FALSE, $this->controller->property('propFalse'));
    $this->assertNull($this->controller->property('propNonExisting'));
  }

}

class TestController extends AbstractEntityController { }

class TestWithBundleController extends AbstractEntityController {

  public static function getClassEntityType() {
    return 'testtype123';
  }

  public static function getClassEntityBundle() {
    return 'testbundle123';
  }

}
