<?php

/**
 * @file
 * Contains \Drupal\ajax_test\Form\AjaxTestDialogForm.
 */

namespace Drupal\ajax_test\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Component\Utility\String;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;

/**
 * Dummy form for testing DialogController with _form routes.
 */
class AjaxTestDialogForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ajax_test_dialog_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // In order to use WebTestBase::drupalPostAjaxForm() to POST from a link, we need
    // to have a dummy field we can set in WebTestBase::drupalPostForm() else it won't
    // submit anything.
    $form['textfield'] = array(
      '#type' => 'hidden'
    );
    $form['button1'] = array(
      '#type' => 'submit',
      '#name' => 'button1',
      '#value' => 'Button 1 (modal)',
      '#ajax' => array(
        'callback' => array($this, 'modal'),
      ),
    );
    $form['button2'] = array(
      '#type' => 'submit',
      '#name' => 'button2',
      '#value' => 'Button 2 (non-modal)',
      '#ajax' => array(
        'callback' => array($this, 'nonModal'),
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $form_state['redirect'] = 'ajax-test/dialog-contents';
  }


  /**
   * AJAX callback handler for AjaxTestDialogForm.
   */
  public function modal(&$form, &$form_state) {
    return $this->dialog(TRUE);
  }

  /**
   * AJAX callback handler for AjaxTestDialogForm.
   */
  public function nonModal(&$form, &$form_state) {
    return $this->dialog(FALSE);
  }


  /**
   * Util to render dialog in ajax callback.
   *
   * @param bool $is_modal
   *   (optional) TRUE if modal, FALSE if plain dialog. Defaults to FALSE.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response object.
   */
  protected function dialog($is_modal = FALSE) {
    $content = ajax_test_dialog_contents();
    $response = new AjaxResponse();
    $title = $this->t('AJAX Dialog contents');
    $html = drupal_render($content);
    if ($is_modal) {
      $response->addCommand(new OpenModalDialogCommand($title, $html));
    }
    else {
      $selector = '#ajax-test-dialog-wrapper-1';
      $response->addCommand(new OpenDialogCommand($selector, $title, $html));
    }
    return $response;
  }

}
