<?php

/**
 * @file
 * Contains \Drupal\checklist\Form\ChecklistSettingsForm.
 */

namespace Drupal\checklist\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\Context\ContextInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure book settings for this site.
 */
class ChecklistSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checklist_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $active = array(0 => t('Unchecked'), 1 => t('Checked'));
    $config = $this->configFactory->get('checklist.settings');
    $checklist_radio = $config->get('checklist_radio');

    $form['radio_one'] = array(
      '#type' => 'radios',
      '#title' => t('radio one'),
      '#options' => $active,
      '#default_value' => isset($checklist_radio) ? $checklist_radio : NULL,
    );

    $form['radio_two'] = array(
      '#type' => 'radios',
      '#title' => t('radio two'),
      '#options' => $active,
      '#default_value' => isset($checklist_radio) ? $checklist_radio : NULL,
    );

    $form['radio_three'] = array(
      '#type' => 'radios',
      '#title' => t('radio three'),
      '#options' => $active,
      '#default_value' => isset($checklist_radio) ? $checklist_radio : NULL,
    );

    $form['radio_four'] = array(
      '#type' => 'radios',
      '#title' => t('radio four'),
      '#options' => $active,
      '#default_value' => isset($checklist_radio) ? $checklist_radio : NULL,
    );

    $form['radio_five'] = array(
      '#type' => 'radios',
      '#title' => t('radio five'),
      '#options' => $active,
      '#default_value' => isset($checklist_radio) ? $checklist_radio : NULL,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->configFactory->get('checklist.settings')
      ->set('checklist_radio', $form_state['values']['radio_one'])
      ->set('checklist_radio', $form_state['values']['radio_two'])
      ->set('checklist_radio', $form_state['values']['radio_three'])
      ->set('checklist_radio', $form_state['values']['radio_four'])
      ->set('checklist_radio', $form_state['values']['radio_five'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}


