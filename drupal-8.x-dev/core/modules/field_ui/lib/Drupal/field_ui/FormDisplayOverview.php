<?php

/**
 * @file
 * Contains \Drupal\field_ui\FormDisplayOverview.
 */

namespace Drupal\field_ui;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field UI form display overview form.
 */
class FormDisplayOverview extends DisplayOverviewBase {

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'form';

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
  public function getFormId() {
    return 'field_ui_form_display_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $entity_type_id = NULL, $bundle = NULL) {
    if ($this->getRequest()->attributes->has('form_mode_name')) {
      $this->mode = $this->getRequest()->attributes->get('form_mode_name');
    }

    return parent::buildForm($form, $form_state, $entity_type_id, $bundle);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildFieldRow(FieldDefinitionInterface $field_definition, EntityDisplayInterface $entity_display, array $form, array &$form_state) {
    $field_row = parent::buildFieldRow($field_definition, $entity_display, $form, $form_state);

    $field_name = $field_definition->getName();

    // Update the (invisible) title of the 'plugin' column.
    $field_row['plugin']['#title'] = $this->t('Formatter for @title', array('@title' => $field_definition->getLabel()));
    if (!empty($field_row['plugin']['settings_edit_form']) && ($plugin = $entity_display->getRenderer($field_name))) {
      $plugin_type_info = $plugin->getPluginDefinition();
      $field_row['plugin']['settings_edit_form']['label']['#markup'] = $this->t('Widget settings:') . ' <span class="plugin-name">' . $plugin_type_info['label'] . '</span>';
    }

    return $field_row;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityDisplay($mode) {
    return entity_get_form_display($this->entity_type, $this->bundle, $mode);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPlugin(FieldDefinitionInterface $field_definition, $configuration) {
    $plugin = NULL;

    if ($configuration && $configuration['type'] != 'hidden') {
      $plugin = $this->pluginManager->getInstance(array(
        'field_definition' => $field_definition,
        'form_mode' => $this->mode,
        'configuration' => $configuration
      ));
    }

    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginOptions($field_type) {
    return parent::getPluginOptions($field_type) + array('hidden' => '- ' . t('Hidden') . ' -');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultPlugin($field_type) {
    return $this->fieldTypes[$field_type]['default_widget'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayModes() {
    return entity_get_form_modes($this->entity_type);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayType() {
    return 'entity_form_display';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTableHeader() {
    return array(
      $this->t('Field'),
      $this->t('Weight'),
      $this->t('Parent'),
      array('data' => $this->t('Widget'), 'colspan' => 3),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getOverviewRoute($mode) {
    return array(
      'route_name' => 'field_ui.form_display_overview_form_mode_' . $this->entity_type,
      'route_parameters' => array(
        $this->bundleEntityType => $this->bundle,
        'form_mode_name' => $mode,
      ),
      'options' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterSettingsForm(array &$settings_form, $plugin, FieldDefinitionInterface $field_definition, array $form, array &$form_state) {
    $context = array(
      'widget' => $plugin,
      'field_definition' => $field_definition,
      'form_mode' => $this->mode,
      'form' => $form,
    );
    drupal_alter('field_widget_settings_form', $settings_form, $form_state, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterSettingsSummary(array &$summary, $plugin, FieldDefinitionInterface $field_definition) {
    $context = array(
      'widget' => $plugin,
      'field_definition' => $field_definition,
      'form_mode' => $this->mode,
    );
    drupal_alter('field_widget_settings_summary', $summary, $context);
  }

}
