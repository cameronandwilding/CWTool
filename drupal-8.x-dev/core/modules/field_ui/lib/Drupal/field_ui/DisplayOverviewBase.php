<?php

/**
 * @file
 * Contains \Drupal\field_ui\DisplayOverviewBase.
 */

namespace Drupal\field_ui;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field UI display overview base class.
 */
abstract class DisplayOverviewBase extends OverviewBase {

  /**
   * The display context. Either 'view' or 'form'.
   *
   * @var string
   */
  protected $displayContext;

  /**
   * The widget or formatter plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerBase
   */
  protected $pluginManager;

  /**
   * A list of field types.
   *
   * @var array
   */
  protected $fieldTypes;

  /**
   * Constructs a new DisplayOverviewBase.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager.
   * @param \Drupal\Component\Plugin\PluginManagerBase $plugin_manager
   *   The widget or formatter plugin manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, FieldTypePluginManagerInterface $field_type_manager, PluginManagerBase $plugin_manager) {
    parent::__construct($entity_manager);

    $this->fieldTypes = $field_type_manager->getConfigurableDefinitions();
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.widget')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    return array(
      'content' => array(
        'title' => $this->t('Content'),
        'invisible' => TRUE,
        'message' => $this->t('No field is displayed.')
      ),
      'hidden' => array(
        'title' => $this->t('Disabled'),
        'message' => $this->t('No field is hidden.')
      ),
    );
  }

  /**
   * Collects the definitions of fields whose display is configurable.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   The array of field definitions
   */
  protected function getFieldDefinitions() {
    // @todo Replace this entire implementation with
    //   \Drupal::entityManager()->getFieldDefinition() when it can hand the
    //   $instance objects - https://drupal.org/node/2114707
    $entity = _field_create_entity_from_ids((object) array('entity_type' => $this->entity_type, 'bundle' => $this->bundle, 'entity_id' => NULL));
    $field_definitions = array();
    foreach ($entity as $field_name => $items) {
      $field_definitions[$field_name] = $items->getFieldDefinition();
    }

    $context = $this->displayContext;
    return array_filter($field_definitions, function(FieldDefinitionInterface $field_definition) use ($context) {
      return $field_definition->isDisplayConfigurable($context);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $entity_type_id = NULL, $bundle = NULL) {
    parent::buildForm($form, $form_state, $entity_type_id, $bundle);

    if (empty($this->mode)) {
      $this->mode = 'default';
    }

    $field_definitions = $this->getFieldDefinitions();
    $extra_fields = $this->getExtraFields();
    $entity_display = $this->getEntityDisplay($this->mode);

    $form_state += array(
      'plugin_settings_edit' => NULL,
    );

    $form += array(
      '#entity_type' => $this->entity_type,
      '#bundle' => $this->bundle,
      '#mode' => $this->mode,
      '#fields' => array_keys($field_definitions),
      '#extra' => array_keys($extra_fields),
    );

    if (empty($field_definitions) && empty($extra_fields) && $route_info = FieldUI::getOverviewRouteInfo($this->entity_type, $this->bundle)) {
      drupal_set_message($this->t('There are no fields yet added. You can add new fields on the <a href="@link">Manage fields</a> page.', array('@link' => $this->url($route_info['route_name'], $route_info['route_parameters'], $route_info['options']))), 'warning');
      return $form;
    }

    $table = array(
      '#type' => 'field_ui_table',
      '#pre_render' => array(array($this, 'tablePreRender')),
      '#tree' => TRUE,
      '#header' => $this->getTableHeader(),
      '#regions' => $this->getRegions(),
      '#attributes' => array(
        'class' => array('field-ui-overview'),
        'id' => 'field-display-overview',
      ),
      // Add Ajax wrapper.
      '#prefix' => '<div id="field-display-overview-wrapper">',
      '#suffix' => '</div>',
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'field-weight',
        ),
        array(
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'field-parent',
          'subgroup' => 'field-parent',
          'source' => 'field-name',
        ),
      ),
    );

    // Field rows.
    foreach ($field_definitions as $field_name => $field_definition) {
      $table[$field_name] = $this->buildFieldRow($field_definition, $entity_display, $form, $form_state);
    }

    // Non-field elements.
    foreach ($extra_fields as $field_id => $extra_field) {
      $table[$field_id] = $this->buildExtraFieldRow($field_id, $extra_field, $entity_display);
    }

    $form['fields'] = $table;

    // Custom display settings.
    if ($this->mode == 'default') {
      // Only show the settings if there is at least one custom display mode.
      if ($display_modes = $this->getDisplayModes()) {
        $form['modes'] = array(
          '#type' => 'details',
          '#title' => $this->t('Custom display settings'),
          '#collapsed' => TRUE,
        );
        // Collect options and default values for the 'Custom display settings'
        // checkboxes.
        $options = array();
        $default = array();
        $display_statuses = $this->getDisplayStatuses();
        foreach ($display_modes as $mode_name => $mode_info) {
          $options[$mode_name] = $mode_info['label'];
          if (!empty($display_statuses[$mode_name])) {
            $default[] = $mode_name;
          }
        }
        $form['modes']['display_modes_custom'] = array(
          '#type' => 'checkboxes',
          '#title' => $this->t('Use custom display settings for the following modes'),
          '#options' => $options,
          '#default_value' => $default,
        );
      }
    }

    // In overviews involving nested rows from contributed modules (i.e
    // field_group), the 'plugin type' selects can trigger a series of changes
    // in child rows. The #ajax behavior is therefore not attached directly to
    // the selects, but triggered by the client-side script through a hidden
    // #ajax 'Refresh' button. A hidden 'refresh_rows' input tracks the name of
    // affected rows.
    $form['refresh_rows'] = array('#type' => 'hidden');
    $form['refresh'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Refresh'),
      '#op' => 'refresh_table',
      '#submit' => array(array($this, 'multistepSubmit')),
      '#ajax' => array(
        'callback' => array($this, 'multistepAjax'),
        'wrapper' => 'field-display-overview-wrapper',
        'effect' => 'fade',
        // The button stays hidden, so we hide the Ajax spinner too. Ad-hoc
        // spinners will be added manually by the client-side script.
        'progress' => 'none',
      ),
      '#attributes' => array('class' => array('visually-hidden'))
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array('#type' => 'submit', '#value' => $this->t('Save'));

    $form['#attached']['library'][] = array('field_ui', 'drupal.field_ui');

    return $form;
  }

  /**
   * Builds the table row structure for a single field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $entity_display
   *   The entity display.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   A reference to a keyed array containing the current state of the form.
   *
   * @return array
   *   A table row array.
   */
  protected function buildFieldRow(FieldDefinitionInterface $field_definition, EntityDisplayInterface $entity_display, array $form, array &$form_state) {
    $field_name = $field_definition->getName();
    $display_options = $entity_display->getComponent($field_name);
    $label = $field_definition->getLabel();

    $field_row = array(
      '#attributes' => array('class' => array('draggable', 'tabledrag-leaf')),
      '#row_type' => 'field',
      '#region_callback' => array($this, 'getRowRegion'),
      '#js_settings' => array(
        'rowHandler' => 'field',
        'defaultPlugin' => $this->getDefaultPlugin($field_definition->getType()),
      ),
      'human_name' => array(
        '#markup' => check_plain($label),
      ),
      'weight' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', array('@title' => $label)),
        '#title_display' => 'invisible',
        '#default_value' => $display_options ? $display_options['weight'] : '0',
        '#size' => 3,
        '#attributes' => array('class' => array('field-weight')),
      ),
      'parent_wrapper' => array(
        'parent' => array(
          '#type' => 'select',
          '#title' => $this->t('Label display for @title', array('@title' => $label)),
          '#title_display' => 'invisible',
          '#options' => drupal_map_assoc(array_keys($this->getRegions())),
          '#empty_value' => '',
          '#attributes' => array('class' => array('field-parent')),
          '#parents' => array('fields', $field_name, 'parent'),
        ),
        'hidden_name' => array(
          '#type' => 'hidden',
          '#default_value' => $field_name,
          '#attributes' => array('class' => array('field-name')),
        ),
      ),

    );

    $field_row['plugin'] = array(
      'type' => array(
        '#type' => 'select',
        '#title' => $this->t('Plugin for @title', array('@title' => $label)),
        '#title_display' => 'invisible',
        '#options' => $this->getPluginOptions($field_definition->getType()),
        '#default_value' => $display_options ? $display_options['type'] : 'hidden',
        '#parents' => array('fields', $field_name, 'type'),
        '#attributes' => array('class' => array('field-plugin-type')),
      ),
      'settings_edit_form' => array(),
    );

    // Check the currently selected plugin, and merge persisted values for its
    // settings.
    if (isset($form_state['values']['fields'][$field_name]['type'])) {
      $display_options['type'] = $form_state['values']['fields'][$field_name]['type'];
    }
    if (isset($form_state['plugin_settings'][$field_name])) {
      $display_options['settings'] = $form_state['plugin_settings'][$field_name];
    }

    // Get the corresponding plugin object.
    $plugin = $this->getPlugin($field_definition, $display_options);

    // Base button element for the various plugin settings actions.
    $base_button = array(
      '#submit' => array(array($this, 'multistepSubmit')),
      '#ajax' => array(
        'callback' => array($this, 'multistepAjax'),
        'wrapper' => 'field-display-overview-wrapper',
        'effect' => 'fade',
      ),
      '#field_name' => $field_name,
    );

    if ($form_state['plugin_settings_edit'] == $field_name) {
      // We are currently editing this field's plugin settings. Display the
      // settings form and submit buttons.
      $field_row['plugin']['settings_edit_form'] = array();

      if ($plugin) {
        // Generate the settings form and allow other modules to alter it.
        $settings_form = $plugin->settingsForm($form, $form_state);
        $this->alterSettingsForm($settings_form, $plugin, $field_definition, $form, $form_state);

        if ($settings_form) {
          $field_row['plugin']['#cell_attributes'] = array('colspan' => 3);
          $field_row['plugin']['settings_edit_form'] = array(
            '#type' => 'container',
            '#attributes' => array('class' => array('field-plugin-settings-edit-form')),
            '#parents' => array('fields', $field_name, 'settings_edit_form'),
            'label' => array(
              '#markup' => $this->t('Plugin settings'),
            ),
            'settings' => $settings_form,
            'actions' => array(
              '#type' => 'actions',
              'save_settings' => $base_button + array(
                '#type' => 'submit',
                '#name' => $field_name . '_plugin_settings_update',
                '#value' => $this->t('Update'),
                '#op' => 'update',
              ),
              'cancel_settings' => $base_button + array(
                '#type' => 'submit',
                '#name' => $field_name . '_plugin_settings_cancel',
                '#value' => $this->t('Cancel'),
                '#op' => 'cancel',
                // Do not check errors for the 'Cancel' button, but make sure we
                // get the value of the 'plugin type' select.
                '#limit_validation_errors' => array(array('fields', $field_name, 'type')),
              ),
            ),
          );
          $field_row['#attributes']['class'][] = 'field-plugin-settings-editing';
        }
      }
    }
    else {
      $field_row['settings_summary'] = array();
      $field_row['settings_edit'] = array();

      if ($plugin) {
        // Display a summary of the current plugin settings, and (if the
        // summary is not empty) a button to edit them.
        $summary = $plugin->settingsSummary();

        // Allow other modules to alter the summary.
        $this->alterSettingsSummary($summary, $plugin, $field_definition);

        if (!empty($summary)) {
          $field_row['settings_summary'] = array(
            '#markup' => '<div class="field-plugin-summary">' . implode('<br />', $summary) . '</div>',
            '#cell_attributes' => array('class' => array('field-plugin-summary-cell')),
          );
        }

        // Check selected plugin settings to display edit link or not.
        $plugin_definition = $plugin->getPluginDefinition();
        if ($plugin_definition['settings']) {
          $field_row['settings_edit'] = $base_button + array(
            '#type' => 'image_button',
            '#name' => $field_name . '_settings_edit',
            '#src' => 'core/misc/configure-dark.png',
            '#attributes' => array('class' => array('field-plugin-settings-edit'), 'alt' => $this->t('Edit')),
            '#op' => 'edit',
            // Do not check errors for the 'Edit' button, but make sure we get
            // the value of the 'plugin type' select.
            '#limit_validation_errors' => array(array('fields', $field_name, 'type')),
            '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
            '#suffix' => '</div>',
          );
        }
      }
    }

    return $field_row;
  }

  /**
   * Builds the table row structure for a single extra field.
   *
   * @param string $field_id
   *   The field ID.
   * @param array $extra_field
   *   The pseudo-field element.
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $entity_display
   *   The entity display.
   *
   * @return array
   *   A table row array.
   */
  protected function buildExtraFieldRow($field_id, $extra_field, EntityDisplayInterface $entity_display) {
    $display_options = $entity_display->getComponent($field_id);

    $extra_field_row = array(
      '#attributes' => array('class' => array('draggable', 'tabledrag-leaf')),
      '#row_type' => 'extra_field',
      '#region_callback' => array($this, 'getRowRegion'),
      '#js_settings' => array('rowHandler' => 'field'),
      'human_name' => array(
        '#markup' => $extra_field['label'],
      ),
      'weight' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', array('@title' => $extra_field['label'])),
        '#title_display' => 'invisible',
        '#default_value' => $display_options ? $display_options['weight'] : 0,
        '#size' => 3,
        '#attributes' => array('class' => array('field-weight')),
      ),
      'parent_wrapper' => array(
        'parent' => array(
          '#type' => 'select',
          '#title' => $this->t('Parents for @title', array('@title' => $extra_field['label'])),
          '#title_display' => 'invisible',
          '#options' => drupal_map_assoc(array_keys($this->getRegions())),
          '#empty_value' => '',
          '#attributes' => array('class' => array('field-parent')),
          '#parents' => array('fields', $field_id, 'parent'),
        ),
        'hidden_name' => array(
          '#type' => 'hidden',
          '#default_value' => $field_id,
          '#attributes' => array('class' => array('field-name')),
        ),
      ),
      'plugin' => array(
        'type' => array(
          '#type' => 'select',
          '#title' => $this->t('Visibility for @title', array('@title' => $extra_field['label'])),
          '#title_display' => 'invisible',
          '#options' => $this->getExtraFieldVisibilityOptions(),
          '#default_value' => $display_options ? 'visible' : 'hidden',
          '#parents' => array('fields', $field_id, 'type'),
          '#attributes' => array('class' => array('field-plugin-type')),
        ),
      ),
      'settings_summary' => array(),
      'settings_edit' => array(),
    );

    return $extra_field_row;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $form_values = $form_state['values'];
    $display = $this->getEntityDisplay($this->mode);

    // Collect data for 'regular' fields.
    foreach ($form['#fields'] as $field_name) {
      // Retrieve the stored instance settings to merge with the incoming
      // values.
      $values = $form_values['fields'][$field_name];

      if ($values['type'] == 'hidden') {
        $display->removeComponent($field_name);
      }
      else {
        // Get plugin settings. They lie either directly in submitted form
        // values (if the whole form was submitted while some plugin settings
        // were being edited), or have been persisted in $form_state.
        $settings = array();
        if (isset($values['settings_edit_form']['settings'])) {
          $settings = $values['settings_edit_form']['settings'];
        }
        elseif (isset($form_state['plugin_settings'][$field_name])) {
          $settings = $form_state['plugin_settings'][$field_name];
        }
        elseif ($current_options = $display->getComponent($field_name)) {
          $settings = $current_options['settings'];
        }

        // Only save settings actually used by the selected plugin.
        $default_settings = $this->pluginManager->getDefaultSettings($values['type']);
        $settings = array_intersect_key($settings, $default_settings);

        // Default component values.
        $component_values = array(
          'type' => $values['type'],
          'weight' => $values['weight'],
          'settings' => $settings
        );

        // Only formatters have configurable label visibility.
        if (isset($values['label'])) {
          $component_values['label'] = $values['label'];
        }

        $display->setComponent($field_name, $component_values);
      }
    }

    // Collect data for 'extra' fields.
    foreach ($form['#extra'] as $name) {
      if ($form_values['fields'][$name]['type'] == 'hidden') {
        $display->removeComponent($name);
      }
      else {
        $display->setComponent($name, array(
          'weight' => $form_values['fields'][$name]['weight'],
        ));
      }
    }

    // Save the display.
    $display->save();

    // Handle the 'display modes' checkboxes if present.
    if ($this->mode == 'default' && !empty($form_values['display_modes_custom'])) {
      $display_modes = $this->getDisplayModes();
      $current_statuses = $this->getDisplayStatuses();

      $statuses = array();
      foreach ($form_values['display_modes_custom'] as $mode => $value) {
        if (!empty($value) && empty($current_statuses[$mode])) {
          // If no display exists for the newly enabled view mode, initialize
          // it with those from the 'default' view mode, which were used so
          // far.
          if (!entity_load($this->getEntityDisplay('default')->getEntityTypeId(), $this->entity_type . '.' . $this->bundle . '.' . $mode)) {
            $display = $this->getEntityDisplay('default')->createCopy($mode);
            $display->save();
          }

          $display_mode_label = $display_modes[$mode]['label'];
          $route = $this->getOverviewRoute($mode);
          drupal_set_message($this->t('The %display_mode mode now uses custom display settings. You might want to <a href="@url">configure them</a>.', array('%display_mode' => $display_mode_label, '@url' => $this->url($route['route_name'], $route['route_parameters'], $route['options']))));
        }
        $statuses[$mode] = !empty($value);
      }

      $this->saveDisplayStatuses($statuses);
    }

    drupal_set_message($this->t('Your settings have been saved.'));
  }

  /**
   * Form submission handler for multistep buttons.
   */
  public function multistepSubmit($form, &$form_state) {
    $trigger = $form_state['triggering_element'];
    $op = $trigger['#op'];

    switch ($op) {
      case 'edit':
        // Store the field whose settings are currently being edited.
        $field_name = $trigger['#field_name'];
        $form_state['plugin_settings_edit'] = $field_name;
        break;

      case 'update':
        // Store the saved settings, and set the field back to 'non edit' mode.
        $field_name = $trigger['#field_name'];
        $values = $form_state['values']['fields'][$field_name]['settings_edit_form']['settings'];
        $form_state['plugin_settings'][$field_name] = $values;
        unset($form_state['plugin_settings_edit']);
        break;

      case 'cancel':
        // Set the field back to 'non edit' mode.
        unset($form_state['plugin_settings_edit']);
        break;

      case 'refresh_table':
        // If the currently edited field is one of the rows to be refreshed, set
        // it back to 'non edit' mode.
        $updated_rows = explode(' ', $form_state['values']['refresh_rows']);
        if (isset($form_state['plugin_settings_edit']) && in_array($form_state['plugin_settings_edit'], $updated_rows)) {
          unset($form_state['plugin_settings_edit']);
        }
        break;
    }

    $form_state['rebuild'] = TRUE;
  }

  /**
   * Ajax handler for multistep buttons.
   */
  public function multistepAjax($form, &$form_state) {
    $trigger = $form_state['triggering_element'];
    $op = $trigger['#op'];

    // Pick the elements that need to receive the ajax-new-content effect.
    switch ($op) {
      case 'edit':
        $updated_rows = array($trigger['#field_name']);
        $updated_columns = array('plugin');
        break;

      case 'update':
      case 'cancel':
        $updated_rows = array($trigger['#field_name']);
        $updated_columns = array('plugin', 'settings_summary', 'settings_edit');
        break;

      case 'refresh_table':
        $updated_rows = array_values(explode(' ', $form_state['values']['refresh_rows']));
        $updated_columns = array('settings_summary', 'settings_edit');
        break;
    }

    foreach ($updated_rows as $name) {
      foreach ($updated_columns as $key) {
        $element = &$form['fields'][$name][$key];
        $element['#prefix'] = '<div class="ajax-new-content">' . (isset($element['#prefix']) ? $element['#prefix'] : '');
        $element['#suffix'] = (isset($element['#suffix']) ? $element['#suffix'] : '') . '</div>';
      }
    }

    // Return the whole table.
    return $form['fields'];
  }

  /**
   * Returns the entity display object used by this form.
   *
   * @param string $mode
   *   A view or form mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityDisplayInterface
   *   An entity display.
   */
  abstract protected function getEntityDisplay($mode);

  /**
   * Returns the extra fields of the entity type and bundle used by this form.
   *
   * @return array
   *   An array of extra field info, as provided by field_info_extra_fields().
   */
  protected function getExtraFields() {
    return field_info_extra_fields($this->entity_type, $this->bundle, ($this->displayContext == 'view' ? 'display' : $this->displayContext));
  }

  /**
   * Returns the widget or formatter plugin for a field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field instance.
   * @param array $configuration
   *   The plugin configuration
   *
   * @return object
   *   The corresponding plugin.
   */
  abstract protected function getPlugin(FieldDefinitionInterface $field_definition, $configuration);

  /**
   * Returns an array of widget or formatter options for a field type.
   *
   * @param string $field_type
   *   The name of the field type.
   *
   * @return array
   *   An array of widget or formatter options.
   */
  protected function getPluginOptions($field_type) {
    return $this->pluginManager->getOptions($field_type);
  }

  /**
   * Returns the ID of the default widget or formatter plugin for a field type.
   *
   * @param string $field_type
   *   The field type.
   *
   * @return string
   *   The widget or formatter plugin ID.
   */
  abstract protected function getDefaultPlugin($field_type);

  /**
   * Returns the form or view modes used by this form.
   *
   * @return array
   *   An array of form or view mode info.
   */
  abstract protected function getDisplayModes();

  /**
   * Returns the display entity type.
   *
   * @return string
   *   The name of the display entity type.
   */
  abstract protected function getDisplayType();

  /**
   * Returns the region to which a row in the display overview belongs.
   *
   * @param array $row
   *   The row element.
   *
   * @return string|null
   *   The region name this row belongs to.
   */
  public function getRowRegion($row) {
    switch ($row['#row_type']) {
      case 'field':
      case 'extra_field':
        return ($row['plugin']['type']['#value'] == 'hidden' ? 'hidden' : 'content');
    }
  }

  /**
   * Returns an array of visibility options for extra fields.
   *
   * @return array
   *   An array of visibility options.
   */
  protected function getExtraFieldVisibilityOptions() {
    return array(
      'visible' => $this->t('Visible'),
      'hidden' => '- ' . $this->t('Hidden') . ' -',
    );
  }

  /**
   * Returns entity (form) displays for the current entity display type.
   *
   * @return array
   *   An array holding entity displays or entity form displays.
   */
  protected function getDisplays() {
    $load_ids = array();
    $display_entity_type = $this->getDisplayType();
    $entity_type = $this->entityManager->getDefinition($display_entity_type);
    $config_prefix = $entity_type->getConfigPrefix();
    $ids = config_get_storage_names_with_prefix($config_prefix . '.' . $this->entity_type . '.' . $this->bundle . '.');
    foreach ($ids as $id) {
      $config_id = str_replace($config_prefix . '.', '', $id);
      list(,, $display_mode) = explode('.', $config_id);
      if ($display_mode != 'default') {
        $load_ids[] = $config_id;
      }
    }
    return entity_load_multiple($display_entity_type, $load_ids);
  }

  /**
   * Returns form or view modes statuses for the bundle used by this form.
   *
   * @return array
   *   An array of form or view mode statuses.
   */
  protected function getDisplayStatuses() {
    $display_statuses = array();
    $displays = $this->getDisplays();
    foreach ($displays as $display) {
      $display_statuses[$display->get('mode')] = $display->status();
    }
    return $display_statuses;
  }

  /**
   * Saves the updated display mode statuses.
   *
   * @param array $display_statuses
   *   An array holding updated form or view mode statuses.
   */
  protected function saveDisplayStatuses($display_statuses) {
    $displays = $this->getDisplays();
    foreach ($displays as $display) {
      $display->set('status', $display_statuses[$display->get('mode')]);
      $display->save();
    }
  }

  /**
   * Returns an array containing the table headers.
   *
   * @return array
   *   The table header.
   */
  abstract protected function getTableHeader();

  /**
   * Returns the route info of a specific form or view mode form.
   *
   * @param string $mode
   *   The form or view mode.
   *
   * @return array
   *   An associative array with the following keys:
   *   - route_name: The name of the route.
   *   - route_parameters: (optional) An associative array of parameter names
   *     and values.
   *   - options: (optional) An associative array of additional options. See
   *     \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *     comprehensive documentation.
   */
  abstract protected function getOverviewRoute($mode);

  /**
   * Alters the widget or formatter settings form.
   *
   * @param array $settings_form
   *   The widget or formatter settings form.
   * @param object $plugin
   *   The widget or formatter.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $form
   *   The The (entire) configuration form array.
   * @param array $form_state
   *   The form state.
   */
  abstract protected function alterSettingsForm(array &$settings_form, $plugin, FieldDefinitionInterface $field_definition, array $form, array &$form_state);

  /**
   * Alters the widget or formatter settings summary.
   *
   * @param array $summary
   *   The widget or formatter settings summary.
   * @param object $plugin
   *   The widget or formatter.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   */
  abstract protected function alterSettingsSummary(array &$summary, $plugin, FieldDefinitionInterface $field_definition);

}
