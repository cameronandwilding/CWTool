<?php
/**
 * @file
 */

namespace CW\Params;

/**
 * Class EntityCreationParams
 * @package CW\Params
 */
class EntityCreationParams {

  const FIELD_KEY_VALUE = 'value';

  const FIELD_KEY_TARGET_ID = 'target_id';

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
   */
  public function setExtraAttributes($extraAttributes) {
    $this->extraAttributes = $extraAttributes;
  }

  /**
   * @param $fieldName
   * @param $value
   * @param string $fieldKey
   * @return self
   */
  public function addExtraAttributeField($fieldName, $value, $fieldKey = self::FIELD_KEY_VALUE) {
    if (empty($this->extraAttributes[$fieldName])) {
      $this->extraAttributes[$fieldName] = array(LANGUAGE_NONE => array());
    }

    $this->extraAttributes[$fieldName][LANGUAGE_NONE][] = array($fieldKey => $value);

    // For chaining;
    return $this;
  }

  public function addExtraAttribute($property, $value) {
    $this->extraAttributes = array_merge($this->extraAttributes, array($property => $value));
  }

}
