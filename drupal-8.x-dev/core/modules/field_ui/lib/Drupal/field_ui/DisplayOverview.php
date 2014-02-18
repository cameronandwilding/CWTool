<?php

/**
 * @file
 * Contains \Drupal\field_ui\DisplayOverview.
 */

namespace Drupal\field_ui;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field UI display overview form.
 */
class DisplayOverview extends DisplayOverviewBase {

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'view';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_ui_display_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $entity_type_id = NULL, $bundle = NULL) {
    if ($this->getRequest()->attributes->has('view_mode_name')) {
      $this->mode = $this->getRequest()->attributes->get('view_mode_name');
    }

    return parent::buildForm($form, $form_state, $entity_type_id, $bundle);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildFieldRow(FieldDefinitionInterface $field_definition, EntityDisplayInterface $entity_display, array $form, array &$form_state) {
    $field_row = parent::buildFieldRow($field_definition, $entity_display, $form, $form_state);

    $field_name = $field_definition->getName();
    $display_options = $entity_display->getComponent($field_name);

    // Insert the label column.
    $label = array(
      'label' => array(
        '#type' => 'select',
        '#title' => $this->t('Label display for @title', array('@title' => $field_definition->getLabel())),
        '#title_display' => 'invisible',
        '#options' => $this->getFieldLabelOptions(),
        '#default_value' => $display_options ? $display_options['label'] : 'above',
      ),
    );

    $label_position = array_search('plugin', array_keys($field_row));
    $field_row = array_slice($field_row, 0, $label_position, TRUE) + $label + array_slice($field_row, $label_position, count($field_row) - 1, TRUE);

    // Update the (invisible) title of the 'plugin' column.
    $field_row['plugin']['#title'] = $this->t('Formatter for @title', array('@title' => $field_definition->getLabel()));
    if (!empty($field_row['plugin']['settings_edit_form']) && ($plugin = $entity_display->getRenderer($field_name))) {
      $plugin_type_info = $plugin->getPluginDefinition();
      $field_row['plugin']['settings_edit_form']['label']['#markup'] = $this->t('Format settings:') . ' <span class="plugin-name">' . $plugin_type_info['label'] . '</span>';
    }

    return $field_row;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildExtraFieldRow($field_id, $extra_field, EntityDisplayInterface $entity_display) {
    $extra_field_row = parent::buildExtraFieldRow($field_id, $extra_field, $entity_display);

    // Insert an empty placeholder for the label column.
    $label = array(
      'empty_cell' => array(
        '#markup' => '&nbsp;'
      )
    );
    $label_position = array_search('plugin', array_keys($extra_field_row));
    $extra_field_row = array_slice($extra_field_row, 0, $label_position, TRUE) + $label + array_slice($extra_field_row, $label_position, count($extra_field_row) - 1, TRUE);

    return $extra_field_row;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityDisplay($mode) {
    return entity_get_display($this->entity_type, $this->bundle, $mode);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPlugin(FieldDefinitionInterface $field_definition, $configuration) {
    $plugin = NULL;

    if ($configuration && $configuration['type'] != 'hidden') {
      $plugin = $this->pluginManager->getInstance(array(
        'field_definition' => $field_definition,
        'view_mode' => $this->mode,
        'configuration' => $configuration
      ));
    }

    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginOptions($field_type) {
    return parent::getPluginOptions($field_type) + array('hidden' => '- ' . $this->t('Hidden') . ' -');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultPlugin($field_type) {
    return $this->fieldTypes[$field_type]['default_formatter'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayModes() {
    return entity_get_view_modes($this->entity_type);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayType() {
    return 'entity_view_display';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTableHeader() {
    return array(
      $this->t('Field'),
      $this->t('Weight'),
      $this->t('Parent'),
      $this->t('Label'),
      array('data' => $this->t('Format'), 'colspan' => 3),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getOverviewRoute($mode) {
    return array(
      'route_name' => 'field_ui.display_overview_view_mode_' . $this->entity_type,
      'route_parameters' => array(
        $this->bundleEntityType => $this->bundle,
        'view_mode_name' => $mode,
      ),
      'options' => array(),
    );
  }

  /**
   * Returns an array of visibility options for field labels.
   *
   * @return array
   *   An array of visibility options.
   */
  protected function getFieldLabelOptions() {
    return array(
      'above' => $this->t('Above'),
      'inline' => $this->t('Inline'),
      'hidden' => '- ' . $this->t('Hidden') . ' -',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterSettingsForm(array &$settings_form, $plugin, FieldDefinitionInterface $field_definition, array $form, array &$form_state) {
    $context = array(
      'formatter' => $plugin,
      'field_definition' => $field_definition,
      'view_mode' => $this->mode,
      'form' => $form,
    );
    drupal_alter('field_formatter_settings_form', $settings_form, $form_state, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterSettingsSummary(array &$summary, $plugin, FieldDefinitionInterface $field_definition) {
    $context = array(
      'formatter' => $plugin,
      'field_definition' => $field_definition,
      'view_mode' => $this->mode,
    );
    drupal_alter('field_formatter_settings_summary', $summary, $context);
  }

}
