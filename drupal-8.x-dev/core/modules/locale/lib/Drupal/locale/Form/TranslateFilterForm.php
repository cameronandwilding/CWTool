<?php

/**
 * @file
 * Contains \Drupal\locale\Form\TranslateFilterForm.
 */

namespace Drupal\locale\Form;

/**
 * Provides a filtered translation edit form.
 */
class TranslateFilterForm extends TranslateFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'locale_translate_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $filters = $this->translateFilters();
    $filter_values = $this->translateFilterValues();

    $form['#attached']['css'] = array(
      drupal_get_path('module', 'locale') . '/css/locale.admin.css',
    );

    $form['filters'] = array(
      '#type' => 'details',
      '#title' => $this->t('Filter translatable strings'),
      '#collapsed' => FALSE,
    );
    foreach ($filters as $key => $filter) {
      // Special case for 'string' filter.
      if ($key == 'string') {
        $form['filters']['status']['string'] = array(
          '#type' => 'search',
          '#title' => $filter['title'],
          '#description' => $filter['description'],
          '#default_value' => $filter_values[$key],
        );
      }
      else {
        $empty_option = isset($filter['options'][$filter['default']]) ? $filter['options'][$filter['default']] : '- None -';
        $form['filters']['status'][$key] = array(
          '#title' => $filter['title'],
          '#type' => 'select',
          '#empty_value' => $filter['default'],
          '#empty_option' => $empty_option,
          '#size' => 0,
          '#options' => $filter['options'],
          '#default_value' => $filter_values[$key],
        );
        if (isset($filter['states'])) {
          $form['filters']['status'][$key]['#states'] = $filter['states'];
        }
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
    if (!empty($_SESSION['locale_translate_filter'])) {
      $form['filters']['actions']['reset'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => array(array($this, 'resetForm')),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $filters = $this->translateFilters();
    foreach ($filters as $name => $filter) {
      if (isset($form_state['values'][$name])) {
        $_SESSION['locale_translate_filter'][$name] = $form_state['values'][$name];
      }
    }
    $form_state['redirect_route']['route_name'] = 'locale.translate_page';
  }

  /**
   * Provides a submit handler for the reset button.
   */
  public function resetForm(array &$form, array &$form_state) {
    $_SESSION['locale_translate_filter'] = array();
    $form_state['redirect_route']['route_name'] = 'locale.translate_page';
  }

}
