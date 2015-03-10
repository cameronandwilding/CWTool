<?php
/**
 * @file
 */

namespace CW\Form;

use CW\Controller\AbstractEntityController;
use CW\Factory\EntityControllerFactory;
use CW\Util\FieldUtil;

/**
 * Class NodeFormState
 * @package CW\Form
 *
 * Form state accessor of a node form.
 *
 * @todo make a FieldAccessor interface
 */
class NodeFormState extends FormState {

  public function fieldValue($fieldName, $valueKey = FieldUtil::KEY_VALUE, $lang = LANGUAGE_NONE, $idx = 0) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang][$idx][$valueKey])) {
      return NULL;
    }

    return $this->formState[self::VALUES_KEY][$fieldName][$lang][$idx][$valueKey];
  }

  public function fieldItem($fieldName, $lang = LANGUAGE_NONE, $idx = 0) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang][$idx])) {
      return NULL;
    }

    return $this->formState[self::VALUES_KEY][$fieldName][$lang][$idx];
  }

  public function fieldReferencedEntityController($fieldName, EntityControllerFactory $entityControllerFactory, $lang = LANGUAGE_NONE, $idx = 0) {
    if (!($targetID = $this->fieldValue($fieldName, FieldUtil::KEY_TARGET_ID, $lang, $idx))) {
      return NULL;
    }

    return $entityControllerFactory->initWithId($targetID);
  }

  /**
   * @param string $fieldName
   * @param \CW\Factory\EntityControllerFactory $entityControllerFactory
   * @param string $lang
   * @return AbstractEntityController[]
   */
  public function fieldAllReferencedEntityController($fieldName, EntityControllerFactory $entityControllerFactory, $lang = LANGUAGE_NONE) {
    if (!isset($this->formState[self::VALUES_KEY][$fieldName][$lang])) {
      return array();
    }

    $controllers = array();
    foreach (array_keys($this->formState[self::VALUES_KEY][$fieldName][$lang]) as $idx) {
      $controllers[] = $this->fieldReferencedEntityController($fieldName, $entityControllerFactory, $lang, $idx);
    }

    return array_filter($controllers);
  }

}
