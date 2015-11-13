<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\EntityCreationParams;
use CW\Params\NodeCreationParams;

/**
 * Class EntityCreatorConfigurationExecutor
 *
 * @package CW\Factory
 *
 * Entity specific creation executor.
 *
 * Entity executor item specs:
 * - param (required): subclass of CW\Params\EntityCreationParams
 * - properties (optional): list of entity properties
 * - fields (optional): list of field definitions
 *
 * Field definition can be simple if only the "value" subkey needs to be filled:
 * {@code}
 * field_first_name: Joe
 * {@endcode}
 *
 * Or the field value key can be explicitly defined:
 *
 * {@code}
 * field_long_text:
 *    format: full_html
 *    value: "Hello stranger."
 * {@endcode}
 */
class EntityCreatorConfigurationExecutor extends CreatorConfigurationExecutor {

  // Conf keys.
  const CONF_PARAM = 'param';
  const CONF_PROPERTIES = 'properties';
  const CONF_FIELDS = 'fields';

  protected function setProperties() {
    $props = $this->getConfiguration(self::CONF_PROPERTIES, []);
    $param = $this->getEntityCreationParam();
    foreach ($props as $prop => $value) {
      $param->setProperty($prop, $value);
    }
  }

  protected function setFields() {
    $param = $this->getEntityCreationParam();
    $fields = $this->getConfiguration(self::CONF_FIELDS, []);
    foreach ($fields as $fieldName => $fieldItem) {
      $fieldItem = $this->resolveValue($fieldItem);
      if (!is_array($fieldItem)) {
        $param->setField($fieldName, $fieldItem);
      }
      else {
        $param->setFieldItem($fieldName, $fieldItem);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepare() {
    $this->setProperties();
    $this->setFields();
  }

  /**
   * @return EntityCreationParams
   */
  protected function getEntityCreationParam() {
    return $this->getConfiguration(self::CONF_PARAM);
  }

}
