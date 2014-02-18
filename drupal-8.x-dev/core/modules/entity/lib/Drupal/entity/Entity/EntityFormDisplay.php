<?php

/**
 * @file
 * Contains \Drupal\entity\Entity\EntityFormDisplay.
 */

namespace Drupal\entity\Entity;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\entity\EntityDisplayBase;

/**
 * Configuration entity that contains widget options for all components of a
 * entity form in a given form mode.
 *
 * @EntityType(
 *   id = "entity_form_display",
 *   label = @Translation("Entity form display"),
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController"
 *   },
 *   config_prefix = "entity.form_display",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   }
 * )
 */
class EntityFormDisplay extends EntityDisplayBase implements EntityFormDisplayInterface, \Serializable {

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'form';

  /**
   * Returns the entity_form_display object used to build an entity form.
   *
   * Depending on the configuration of the form mode for the entity bundle, this
   * can be either the display object associated to the form mode, or the
   * 'default' display.
   *
   * This method should only be used internally when rendering an entity form.
   * When assigning suggested display options for a component in a given form
   * mode, entity_get_form_display() should be used instead, in order to avoid
   * inadvertently modifying the output of other form modes that might happen to
   * use the 'default' display too. Those options will then be effectively
   * applied only if the form mode is configured to use them.
   *
   * hook_entity_form_display_alter() is invoked on each display, allowing 3rd
   * party code to alter the display options held in the display before they are
   * used to generate render arrays.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which the form is being built.
   * @param string $form_mode
   *   The form mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   *   The display object that should be used to build the entity form.
   *
   * @see entity_get_form_display()
   * @see hook_entity_form_display_alter()
   */
  public static function collectRenderDisplay($entity, $form_mode) {
    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    // Check the existence and status of:
    // - the display for the form mode,
    // - the 'default' display.
    if ($form_mode != 'default') {
      $candidate_ids[] = $entity_type . '.' . $bundle . '.' . $form_mode;
    }
    $candidate_ids[] = $entity_type . '.' . $bundle . '.default';
    $results = \Drupal::entityQuery('entity_form_display')
      ->condition('id', $candidate_ids)
      ->condition('status', TRUE)
      ->execute();

    // Load the first valid candidate display, if any.
    $storage = \Drupal::entityManager()->getStorageController('entity_form_display');
    foreach ($candidate_ids as $candidate_id) {
      if (isset($results[$candidate_id])) {
        $display = $storage->load($candidate_id);
        break;
      }
    }
    // Else create a fresh runtime object.
    if (empty($display)) {
      $display = $storage->create(array(
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'mode' => $form_mode,
        'status' => TRUE,
      ));
    }

    // Let the display know which form mode was originally requested.
    $display->originalMode = $form_mode;

    // Let modules alter the display.
    $display_context = array(
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'form_mode' => $form_mode,
    );
    \Drupal::moduleHandler()->alter('entity_form_display', $display, $display_context);

    return $display;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    $this->pluginManager = \Drupal::service('plugin.manager.field.widget');

    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderer($field_name) {
    if (isset($this->plugins[$field_name])) {
      return $this->plugins[$field_name];
    }

    // Instantiate the widget object from the stored display properties.
    if (($configuration = $this->getComponent($field_name)) && isset($configuration['type']) && ($definition = $this->getFieldDefinition($field_name))) {
      $widget = $this->pluginManager->getInstance(array(
        'field_definition' => $definition,
        'form_mode' => $this->originalMode,
        // No need to prepare, defaults have been merged in setComponent().
        'prepare' => FALSE,
        'configuration' => $configuration
      ));
    }
    else {
      $widget = NULL;
    }

    // Persist the widget object.
    $this->plugins[$field_name] = $widget;
    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function serialize() {
    // Only store the definition, not external objects or derived data.
    $data = $this->getExportProperties() + array('entityType' => $this->getEntityTypeId());
    return serialize($data);
  }

  /**
   * {@inheritdoc}
   */
  public function unserialize($serialized) {
    $data = unserialize($serialized);
    $entity_type = $data['entityType'];
    unset($data['entityType']);
    $this->__construct($data, $entity_type);
  }

}
