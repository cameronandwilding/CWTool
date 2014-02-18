<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\LanguageItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Language\Language;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'language' entity field item.
 *
 * @FieldType(
 *   id = "language",
 *   label = @Translation("Language"),
 *   description = @Translation("An entity field referencing a language."),
 *   configurable = FALSE,
 *   constraints = {
 *     "ComplexData" = {
 *       "value" = {"Length" = {"max" = 12}}
 *     }
 *   }
 * )
 */
class LanguageItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see LanguageItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = DataDefinition::create('string')
        ->setLabel(t('Language code'));

      static::$propertyDefinitions['language'] = DataDefinition::create('language_reference')
        ->setLabel(t('Language object'))
        ->setDescription(t('The referenced language'))
      // The language object is retrieved via the language code.
        ->setComputed(TRUE)
        ->setReadOnly(FALSE);
    }
    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => 12,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the language property, if no array
    // is given as this handles language codes and objects.
    if (isset($values) && !is_array($values)) {
      // Directly update the property instead of invoking the parent, so that
      // the language property can take care of updating the language code
      // property.
      $this->properties['language']->setValue($values, $notify);
      // If notify was FALSE, ensure the value property gets synched.
      if (!$notify) {
        $this->set('value', $this->properties['language']->getTargetIdentifier(), FALSE);
      }
    }
    else {
      // Make sure that the 'language' property gets set as 'value'.
      if (isset($values['value']) && !isset($values['language'])) {
        $values['language'] = $values['value'];
      }
      parent::setValue($values, $notify);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // Default to LANGCODE_NOT_SPECIFIED.
    $this->setValue(array('value' => Language::LANGCODE_NOT_SPECIFIED), $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name) {
    // Make sure that the value and the language property stay in sync.
    if ($property_name == 'value') {
      $this->properties['language']->setValue($this->value, FALSE);
    }
    elseif ($property_name == 'language') {
      $this->set('value', $this->properties['language']->getTargetIdentifier(), FALSE);
    }
    parent::onChange($property_name);
  }
}
