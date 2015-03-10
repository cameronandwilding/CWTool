<?php
/**
 * @file
 */

namespace CW\Form;

use CW\Factory\EntityControllerFactory;
use CW\Util\FieldUtil;
use CW\Util\FormUtil;

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

}
