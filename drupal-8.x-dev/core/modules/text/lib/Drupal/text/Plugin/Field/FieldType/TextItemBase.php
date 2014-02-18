<?php

/**
 * @file
 * Contains \Drupal\text\Plugin\Field\FieldType\TextItemBase.
 */

namespace Drupal\text\Plugin\Field\FieldType;

use Drupal\Core\Field\ConfigFieldItemBase;
use Drupal\Core\Field\PrepareCacheInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Base class for 'text' configurable field types.
 */
abstract class TextItemBase extends ConfigFieldItemBase implements PrepareCacheInterface {

  /**
   * Definitions of the contained properties.
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

      static::$propertyDefinitions['format'] = DataDefinition::create('filter_format')
        ->setLabel(t('Text format'));

      static::$propertyDefinitions['processed'] = DataDefinition::create('string')
        ->setLabel(t('Processed text'))
        ->setDescription(t('The text value with the text format applied.'))
        ->setComputed(TRUE)
        ->setClass('\Drupal\text\TextProcessed')
        ->setSetting('text source', 'value');
    }
    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // Default to a simple check_plain().
    // @todo: Add in the filter default format here.
    $this->setValue(array('format' => NULL), $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheData() {
    $data = $this->getValue();
    // Where possible, generate the processed (sanitized) version of each
    // textual property (e.g., 'value', 'summary') within this field item early
    // so that it is cached in the field cache. This avoids the need to look up
    // the sanitized value in the filter cache separately.
    $text_processing = $this->getSetting('text_processing');
    if (!$text_processing || filter_format_allowcache($this->get('format')->getValue())) {
      foreach ($this->getPropertyDefinitions() as $property => $definition) {
        if ($definition->getClass() == '\Drupal\text\TextProcessed') {
          $data[$property] = $this->get($property)->getValue();
        }
      }
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name) {
    // Notify the parent of changes.
    if (isset($this->parent)) {
      $this->parent->onChange($this->name);
    }

    // Unset processed properties that are affected by the change.
    foreach ($this->getPropertyDefinitions() as $property => $definition) {
      if ($definition->getClass() == '\Drupal\text\TextProcessed') {
        if ($property_name == 'format' || ($definition->getSetting('text source') == $property_name)) {
          $this->set($property, NULL, FALSE);
        }
      }
    }
  }

}
