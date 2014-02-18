<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\EmailItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'email' entity field type.
 *
 * @FieldType(
 *   id = "email",
 *   label = @Translation("E-mail"),
 *   description = @Translation("An entity field containing an e-mail value."),
 *   configurable = FALSE
 * )
 */
class EmailItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see EmailItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = DataDefinition::create('email')
        ->setLabel(t('E-mail value'));
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
          'length' => EMAIL_MAX_LENGTH,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $constraints[] = $constraint_manager->create('ComplexData', array(
      'value' => array(
        'Length' => array(
          'max' => EMAIL_MAX_LENGTH,
          'maxMessage' => t('%name: the e-mail address can not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max' => EMAIL_MAX_LENGTH)),
        )
      ),
    ));

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->value === NULL || $this->value === '';
  }

}
