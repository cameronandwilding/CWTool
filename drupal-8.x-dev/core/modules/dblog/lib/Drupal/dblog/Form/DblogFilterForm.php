<?php

/**
 * @file
 * Contains \Drupal\dblog\Form\DblogFilterForm.
 */

namespace Drupal\dblog\Form;

use Drupal\Core\Form\FormBase;

/**
 * Provides the database logging filter form.
 */
class DblogFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'dblog_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $filters = dblog_filters();

    $form['filters'] = array(
      '#type' => 'details',
      '#title' => $this->t('Filter log messages'),
      '#collapsed' => empty($_SESSION['dblog_overview_filter']),
    );
    foreach ($filters as $key => $filter) {
      $form['filters']['status'][$key] = array(
        '#title' => $filter['title'],
        '#type' => 'select',
        '#multiple' => TRUE,
        '#size' => 8,
        '#options' => $filter['options'],
      );
      if (!empty($_SESSION['dblog_overview_filter'][$key])) {
        $form['filters']['status'][$key]['#default_value'] = $_SESSION['dblog_overview_filter'][$key];
      }
    }

    $form['filters']['actions'] = array(
      '#type' => 'actions',
      '#attributes' => array('class' => array('container-inline')),
    );
    $form['filters']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    );
    if (!empty($_SESSION['dblog_overview_filter'])) {
      $form['filters']['actions']['reset'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => array(),
        '#submit' => array(array($this, 'resetForm')),
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    if (empty($form_state['values']['type']) && empty($form_state['values']['severity'])) {
      $this->setFormError('type', $form_state, $this->t('You must select something to filter by.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $filters = dblog_filters();
    foreach ($filters as $name => $filter) {
      if (isset($form_state['values'][$name])) {
        $_SESSION['dblog_overview_filter'][$name] = $form_state['values'][$name];
      }
    }
  }

  /**
   * Resets the filter form.
   */
  public function resetForm(array &$form, array &$form_state) {
    $_SESSION['dblog_overview_filter'] = array();
  }

}
