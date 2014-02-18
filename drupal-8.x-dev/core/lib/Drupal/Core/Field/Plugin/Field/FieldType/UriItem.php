<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\UriItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'uri' entity field type.
 *
 * URIs are not length limited by RFC 2616, but we need to provide a sensible
 * default. There is a de-facto limit of 2000 characters in browsers and other
 * implementors, so we go with 2048.
 *
 * @FieldType(
 *   id = "uri",
 *   label = @Translation("URI"),
 *   description = @Translation("An entity field containing a URI."),
 *   settings = {
 *     "max_length" = "2048"
 *   },
 *   configurable = FALSE
 * )
 */
class UriItem extends StringItem {

  /**
   * Field definitions of the contained properties.
   *
   * @see self::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['value'] = DataDefinition::create('uri')
        ->setLabel(t('URI value'));
    }
    return self::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'not null' => TRUE,
        ),
      ),
    );
  }

}
