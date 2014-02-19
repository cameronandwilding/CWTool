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
    $active = array(0 => t('Unchecked'), 1 => t('Checked'));
    $config = $this->config('checklist.settings');


    $form['checklist_1a'] = array(
      '#type' => 'radios',
      '#title' => t('Feeding your Dog'),
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
        ->set('checklist_default', $form_state['values']['checklist_1a'])
        ->save();

    parent::submitForm($form, $form_state);
  }

}


