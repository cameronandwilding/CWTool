<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\StringItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'string' entity field type.
 *
 * @FieldType(
 *   id = "string",
 *   label = @Translation("String"),
 *   description = @Translation("An entity field containing a string value."),
 *   settings = {
 *     "max_length" = "255"
 *   },
 *   configurable = FALSE
 * )
 */
class StringItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see StringItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = DataDefinition::create('string')
        ->setLabel(t('Text value'));
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
          'length' => $field_definition->getSetting('max_length'),
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array('Length' => array('max' => $max_length))
      ));
    }

    return $constraints;
  }

}
