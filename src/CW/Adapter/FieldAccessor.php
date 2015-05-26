<?php
/**
 * @file
 *
 * Field accessor.
 */

namespace CW\Adapter;

use CW\Controller\AbstractEntityController;
use CW\Factory\EntityControllerFactory;
use CW\Util\FieldUtil;

/**
 * Interface FieldAccessor
 * @package CW\Adapter
 *
 * The instance is able to get field values which is compliant with the Drupal
 * field API. Such as entities, form states, etc.
 */
interface FieldAccessor {

  /**
   * Gets a value of a field item.
   *
   * @param string $fieldName
   * @param string $key
   * @param int $idx
   * @param string $lang
   * @return mixed
   */
  public function fieldValue($fieldName, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get the target id of an entity reference.
   *
   * @param string $fieldName
   * @param int $idx
   * @param string $lang
   * @return mixed|null
   */
  public function fieldTargetID($fieldName, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get the file id of an file.
   *
   * @param string $fieldName
   * @param int $idx
   * @param string $lang
   * @return mixed|null
   */
  public function fieldFileID($fieldName, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get the whole field item with all the properties (plus value).
   *
   * @param string $fieldName
   * @param int $idx
   * @param string $lang
   * @return array
   */
  public function fieldItem($fieldName, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get all the field items of a field.
   *
   * @param string $fieldName
   * @param string $lang
   * @return array
   */
  public function fieldItems($fieldName, $lang = LANGUAGE_NONE);

  /**
   * Get the referenced entity controller referenced in the field.
   *
   * @param string $fieldName
   * @param EntityControllerFactory $entityControllerFactory
   * @param int $idx
   * @param string $lang
   * @return AbstractEntityController|NULL
   */
  public function fieldReferencedEntityController($fieldName, EntityControllerFactory $entityControllerFactory, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get all referenced entity controller referenced by the field.
   * The referenced entity type has to be the same, or the factory should be
   * generic.
   *
   * @param string $fieldName
   * @param EntityControllerFactory $entityControllerFactory
   * @param string $lang
   * @return AbstractEntityController[]
   */
  public function fieldAllReferencedEntityController($fieldName, EntityControllerFactory $entityControllerFactory, $lang = LANGUAGE_NONE);

  /**
   * Get the referenced file controller.
   *
   * @param string $fieldName
   * @param EntityControllerFactory $entityFactory
   * @param int $idx
   * @param string $lang
   * @return mixed
   */
  public function fieldReferencedFileCtrl($fieldName, EntityControllerFactory $entityFactory, $idx = 0, $lang = LANGUAGE_NONE);

  /**
   * Get the referenced taxonomy term controller.
   *
   * @param string $fieldName
   * @param EntityControllerFactory $entityFactory
   * @param int $idx
   * @param string $lang
   * @return mixed
   */
  public function fieldReferencedTaxonomyTermCtrl($fieldName, EntityControllerFactory $entityFactory, $idx = 0, $lang = LANGUAGE_NONE);

}
