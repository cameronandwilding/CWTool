<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'entity_reference' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 * - target_bundle: (optional): If set, restricts the entity bundles which may
 *   may be referenced. May be set to an single bundle, or to an array of
 *   allowed bundles.
 *
 * @FieldType(
 *   id = "entity_reference",
 *   label = @Translation("Entity reference"),
 *   description = @Translation("An entity field containing an entity reference."),
 *   configurable = FALSE,
 *   constraints = {"ValidReference" = TRUE}
 * )
 */
class EntityReferenceItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see EntityReferenceItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    $settings = $this->definition->getSettings();
    $target_type = $settings['target_type'];

    // Definitions vary by entity type and bundle, so key them accordingly.
    $key = $target_type . ':';
    $key .= isset($settings['target_bundle']) ? $settings['target_bundle'] : '';

    if (!isset(static::$propertyDefinitions[$key])) {
      $target_type_info = \Drupal::entityManager()->getDefinition($target_type);
      if ($target_type_info->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
        // @todo: Lookup the entity type's ID data type and use it here.
        // https://drupal.org/node/2107249
        static::$propertyDefinitions[$key]['target_id'] = DataDefinition::create('integer')
          ->setLabel(t('Entity ID'))
          ->setConstraints(array(
            'Range' => array('min' => 0),
          ));
      }
      else {
        static::$propertyDefinitions[$key]['target_id'] = DataDefinition::create('string')
          ->setLabel(t('Entity ID'));
      }

      static::$propertyDefinitions[$key]['entity'] = DataDefinition::create('entity_reference')
        ->setLabel(t('Entity'))
        ->setDescription(t('The referenced entity'))
        // The entity object is computed out of the entity ID.
        ->setComputed(TRUE)
        ->setReadOnly(FALSE)
        ->setConstraints(array(
          'EntityType' => $settings['target_type'],
        ));

      if (isset($settings['target_bundle'])) {
        static::$propertyDefinitions[$key]['entity']->addConstraint('Bundle', $settings['target_bundle']);
      }
    }
    return static::$propertyDefinitions[$key];
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getSetting('target_type');
    $target_type_info = \Drupal::entityManager()->getDefinition($target_type);

    if ($target_type_info->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
      $columns = array(
        'target_id' => array(
          'description' => 'The ID of the target entity.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
      );
    }
    else {
      $columns = array(
        'target_id' => array(
          'description' => 'The ID of the target entity.',
          'type' => 'varchar',
          'length' => '255',
        ),
      );
    }

    $schema = array(
      'columns' => $columns,
      'indexes' => array(
        'target_id' => array('target_id'),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $name = ($name == 'value') ? 'target_id' : $name;
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function get($property_name) {
    $property_name = ($property_name == 'value') ? 'target_id' : $property_name;
    return parent::get($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property_name) {
    $property_name = ($property_name == 'value') ? 'target_id' : $property_name;
    return parent::__isset($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values) && !is_array($values)) {
      // Directly update the property instead of invoking the parent, so it can
      // handle objects and IDs.
      $this->properties['entity']->setValue($values, $notify);
      // If notify was FALSE, ensure the target_id property gets synched.
      if (!$notify) {
        $this->set('target_id', $this->properties['entity']->getTargetIdentifier(), FALSE);
      }
    }
    else {
      // Make sure that the 'entity' property gets set as 'target_id'.
      if (isset($values['target_id']) && !isset($values['entity'])) {
        $values['entity'] = $values['target_id'];
      }
      parent::setValue($values, $notify);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValue($include_computed = FALSE) {
    $values = parent::getValue($include_computed);

    // If there is an unsaved entity, return it as part of the field item values
    // to ensure idempotency of getValue() / setValue().
    if ($this->hasUnsavedEntity()) {
      $values['entity'] = $this->entity;
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name) {
    // Make sure that the target ID and the target property stay in sync.
    if ($property_name == 'target_id') {
      $this->properties['entity']->setValue($this->target_id, FALSE);
    }
    elseif ($property_name == 'entity') {
      $this->set('target_id', $this->properties['entity']->getTargetIdentifier(), FALSE);
    }
    parent::onChange($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getMainPropertyName() {
    return 'target_id';
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // Avoid loading the entity by first checking the 'target_id'.
    $target_id = $this->target_id;
    if ($target_id !== NULL) {
      return FALSE;
    }
    // Allow auto-create entities.
    if ($this->hasUnsavedEntity()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    if ($this->hasUnsavedEntity()) {
      $this->entity->save();
      $this->target_id = $this->entity->id();
    }
  }

  /**
   * Determines whether the item holds an unsaved entity.
   *
   * This is notably used for "autocreate" widgets, and more generally to
   * support referencing freshly created entities (they will get saved
   * automatically as the hosting entity gets saved).
   *
   * @return bool
   *   TRUE if the item holds an unsaved entity.
   */
  public function hasUnsavedEntity() {
    return $this->target_id === NULL && ($entity = $this->entity) && $entity->isNew();
  }

}
