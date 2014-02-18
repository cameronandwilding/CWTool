<?php

/**
 * @file
 * Contains \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem.
 */

namespace Drupal\datetime\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\PrepareCacheInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\ConfigFieldItemBase;

/**
 * Plugin implementation of the 'datetime' field type.
 *
 * @FieldType(
 *   id = "datetime",
 *   label = @Translation("Date"),
 *   description = @Translation("Create and store date values."),
 *   settings = {
 *     "datetime_type" = "datetime"
 *   },
 *   default_widget = "datetime_default",
 *   default_formatter = "datetime_default",
 *   list_class = "\Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList"
 * )
 */
class DateTimeItem extends ConfigFieldItemBase implements PrepareCacheInterface {

  /**
   * Value for the 'datetime_type' setting: store only a date.
   */
  const DATETIME_TYPE_DATE = 'date';

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_DATETIME = 'datetime';

  /**
   * Field definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = DataDefinition::create('datetime_iso8601')
        ->setLabel(t('Date value'));

      static::$propertyDefinitions['date'] = DataDefinition::create('datetime_computed')
        ->setLabel(t('Computed date'))
        ->setDescription(t('The computed DateTime object.'))
        ->setComputed(TRUE)
        ->setClass('\Drupal\datetime\DateTimeComputed')
        ->setSetting('date source', 'value');
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
          'description' => 'The date value.',
          'type' => 'varchar',
          'length' => 20,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    $element = array();

    $element['datetime_type'] = array(
      '#type' => 'select',
      '#title' => t('Date type'),
      '#description' => t('Choose the type of date to create.'),
      '#default_value' => $this->getSetting('datetime_type'),
      '#options' => array(
        static::DATETIME_TYPE_DATETIME => t('Date and time'),
        static::DATETIME_TYPE_DATE => t('Date only'),
      ),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheData() {
    $data = $this->getValue();
    // The function generates a Date object for each field early so that it is
    // cached in the field cache. This avoids the need to generate the object
    // later. The date will be retrieved in UTC, the local timezone adjustment
    // must be made in real time, based on the preferences of the site and user.
    if (!empty($data['value'])) {
      $data['date'] = $this->date;
    }
    return $data;
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
  public function onChange($property_name) {
    parent::onChange($property_name);

    // Enforce that the computed date is recalculated.
    if ($property_name == 'value') {
      $this->date = NULL;
    }
  }

}
