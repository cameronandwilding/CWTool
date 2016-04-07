<?php
/**
 * @file
 *
 * Entity creation params.
 */

namespace CW\Params;

use CW\Util\FieldUtil;

/**
 * Class EntityCreationParams
 * @package CW\Params
 */
class EntityCreationParams {

  /**
   * Fields or node properties.
   *
   * Field example:
   * [
   *  'field_text' => [LANGUAGE_NONE => [['value' => 'some text']],
   * ]
   *
   * @var array
   */
  private $extraAttributes = array();

  /**
   * @param array $extraAttributes
   *  Container for any kind of entity data.
   */
  public function __construct(array $extraAttributes = array()) {
    $this->extraAttributes = $extraAttributes;
  }

  /**
   * @return array
   */
  public function getExtraAttributes() {
    return $this->extraAttributes;
  }

  /**
   * @param array $extraAttributes
   * @return $this
   */
  public function setExtraAttributes($extraAttributes) {
    $this->extraAttributes = $extraAttributes;
    return $this;
  }

  /**
   * Set's a field value.
   *
   * @param $fieldName
   * @param $value
   * @param string $fieldKey
   * @param string $lang
   * @return \CW\Params\EntityCreationParams
   */
  public function setField($fieldName, $value, $fieldKey = FieldUtil::KEY_VALUE, $lang = LANGUAGE_NONE) {
    if (empty($this->extraAttributes[$fieldName][$lang])) {
      $this->extraAttributes[$fieldName][$lang] = [];
    }

    $this->extraAttributes[$fieldName][$lang][] = [$fieldKey => $value];

    // For chaining;
    return $this;
  }

  /**
   * @param string $fieldName
   * @param array $value
   * @param string $lang
   * @return $this
   */
  public function setFieldItem($fieldName, array $value, $lang = LANGUAGE_NONE) {
    if (empty($this->extraAttributes[$fieldName][$lang])) {
      $this->extraAttributes[$fieldName][$lang] = [];
    }

    $this->extraAttributes[$fieldName][$lang][] = $value;

    return $this;
  }

  /**
   * Merge safe way of setting custom properties, even arrays with elements deep.
   *
   * @param string $property
   * @param mixed $value
   * @return $this
   */
  public function setProperty($property, $value) {
    $this->extraAttributes = array_merge($this->extraAttributes, array($property => $value));
    return $this;
  }

}
