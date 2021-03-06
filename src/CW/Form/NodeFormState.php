<?php
/**
 * @file
 *
 * Node form state.
 */

namespace CW\Form;

use CW\Adapter\FieldAccessor;
use CW\Factory\EntityControllerFactoryInterface;
use CW\Util\FieldUtil;

/**
 * Class NodeFormState
 * @package CW\Form
 *
 * Form state accessor of a node form.
 */
class NodeFormState extends FormState implements FieldAccessor {

  /**
   * {@inheritdoc}
   */
  public function fieldItem($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang][$idx])) {
      return NULL;
    }

    return $this->formState[self::VALUES_KEY][$fieldName][$lang][$idx];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldItems($fieldName, $lang = LANGUAGE_NONE) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang])) {
      return NULL;
    }

    return $this->formState[self::VALUES_KEY][$fieldName][$lang];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldValue($fieldName, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang][$idx][$key])) {
      return NULL;
    }

    return $this->formState[self::VALUES_KEY][$fieldName][$lang][$idx][$key];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedEntityController($fieldName, EntityControllerFactoryInterface $entityControllerFactory, $fieldKey = FieldUtil::KEY_TARGET_ID, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!($targetID = $this->fieldValue($fieldName, $fieldKey, $idx, $lang))) {
      return NULL;
    }

    return $entityControllerFactory->initWithId($targetID);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldAllReferencedEntityController($fieldName, EntityControllerFactoryInterface $entityControllerFactory, $fieldKey = FieldUtil::KEY_TARGET_ID, $lang = LANGUAGE_NONE) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang])) {
      return array();
    }

    $controllers = array();
    foreach (array_keys($this->formState[self::VALUES_KEY][$fieldName][$lang]) as $idx) {
      $controllers[] = $this->fieldReferencedEntityController($fieldName, $entityControllerFactory, $fieldKey, $idx, $lang);
    }

    return array_filter($controllers);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedFileCtrl($fieldName, EntityControllerFactoryInterface $entityFactory, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!($fid = $this->fieldValue($fieldName, FieldUtil::KEY_FILE_ID, $idx, $lang))) {
      return NULL;
    }

    return $entityFactory->initWithId($fid);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedTaxonomyTermCtrl($fieldName, EntityControllerFactoryInterface $entityFactory, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!($tid = $this->fieldValue($fieldName, FieldUtil::KEY_TAXONOMY_ID, $idx, $lang))) {
      return NULL;
    }

    return $entityFactory->initWithId($tid);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldTargetID($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    $this->fieldValue($fieldName, FieldUtil::KEY_TARGET_ID, $idx, $lang);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldFileID($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    $this->fieldValue($fieldName, FieldUtil::KEY_FILE_ID, $idx, $lang);
  }

}
