<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\EntityCreationParams;

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
    foreach ($fields as $fieldName => $fieldData) {
      $fieldData = $this->resolveValue($fieldData);
      if (!is_array($fieldData)) {
        $param->setField($fieldName, $fieldData);
      }
      elseif (self::isGenericArray($fieldData)) {
        foreach ($fieldData as $fieldItem) {
          $param->setFieldItem($fieldName, $fieldItem);
        }
      }
      else {
        $param->setFieldItem($fieldName, $fieldData);
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

  /**
   * @param mixed $value
   * @return bool
   */
  protected static function isGenericArray($value) {
    if (!is_array($value)) {
      return FALSE;
    }

    $keys = array_keys($value);
    $sample_keys = range(0, count($keys) - 1);
    return $keys === $sample_keys;
  }

}
