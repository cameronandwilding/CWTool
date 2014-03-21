<?php

/**
 * @file
 * Contains \Drupal\checklist\Form\ChecklistSettingsForm.
 */

namespace Drupal\checklist\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure book settings for this site.
 */
class ChecklistSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checklist_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $active = array(0 => t('Left'), 1 => t('Right'));
    $config = $this->config('checklist.settings');

    $form['checkbox'] = array(
      '#type' => 'radios',
      '#title' => t('Position of the checkbox on a checklist '),
      '#options' => $active,
      '#default_value' => $config->get('checklist_default'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('checklist.settings')
        ->set('checklist_default', $form_state['values']['checkbox'])
        ->save();

    parent::submitForm($form, $form_state);
  }
}
