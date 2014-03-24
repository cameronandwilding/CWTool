<?php
/**
 * @file
 * Definition of Drupal\checklist\Entity\Form\ChecklistFormController.
 */

namespace Drupal\checklist\Entity\Form;

use Drupal\Core\Entity\ContentEntityFormController;
use Drupal\Core\Language\Language;

/**
 * Form controller for the checklist item entity edit forms.
 */
class ChecklistFormController extends ContentEntityFormController {
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $checklist_item = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', array('@type' => node_get_type_label($checklist_item), '@title' => $checklist_item->label()));
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => $checklist_item->name->value,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#weight' => -10,
    );

    $form['checkbox_field'] = array(
      '#type' => 'checkbox',
      '#title' => t('Please click here if you have completed this checklist item'),
      '#default_value' => $checklist_item->checkbox_field->value,
    );

    return parent::form($form, $form_state, $checklist_item);
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::submit().
   */
  public function submit(array $form, array &$form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);
    //$form_state['redirect_route']['route_name'] = 'checklist.list';
    return $entity;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $checklist_item = $this->entity;
    $checklist_item->save();
  }
}
