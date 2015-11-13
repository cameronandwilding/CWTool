<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\NodeCreationParams;

class EntityCreatorConfigurationExecutor extends CreatorConfigurationExecutor {

  protected function setProperties() {
    $props = $this->getConfParam('properties', []);
    /** @var NodeCreationParams $param */
    $param = $this->getConfParam('@param');
    foreach ($props as $prop => $value) {
      $param->setProperty($prop, $value);
    }
  }

  protected function setFields() {
    /** @var NodeCreationParams $param */
    $param = $this->getConfParam('@param');
    $fields = $this->getConfParam('fields', []);
    foreach ($fields as $fieldName => $fieldItem) {
      $fieldItem = $this->resolveParamArgs($fieldItem);
      if (!is_array($fieldItem)) {
        $param->setField($fieldName, $fieldItem);
      }
      else {
        $param->setFieldItem($fieldName, $fieldItem);
      }
    }
  }

  protected function prepare() {
    $this->setProperties();
    $this->setFields();
  }

}
