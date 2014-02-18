<?php

/**
 * @file
 * Definition of Drupal\views\Plugin\views\area\Text.
 */

namespace Drupal\views\Plugin\views\area;

/**
 * Views area text handler.
 *
 * @ingroup views_area_handlers
 *
 * @PluginID("text")
 */
class Text extends TokenizeAreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['content'] = array('default' => '', 'translatable' => TRUE, 'format_key' => 'format');
    $options['format'] = array('default' => NULL);
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, &$form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['content'] = array(
      '#title' => t('Content'),
      '#type' => 'text_format',
      '#default_value' => $this->options['content'],
      '#rows' => 6,
      '#format' => isset($this->options['format']) ? $this->options['format'] : filter_default_format(),
      '#editor' => FALSE,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, &$form_state) {
    $form_state['values']['options']['format'] = $form_state['values']['options']['content']['format'];
    $form_state['values']['options']['content'] = $form_state['values']['options']['content']['value'];
    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $format = isset($this->options['format']) ? $this->options['format'] : filter_default_format();
    if (!$empty || !empty($this->options['empty'])) {
      return array(
        '#markup' => $this->renderTextarea($this->options['content'], $format),
      );
    }

    return array();
  }

  /**
   * Render a text area, using the proper format.
   */
  public function renderTextarea($value, $format) {
    if ($value) {
      return check_markup($this->tokenizeValue($value), $format, '', FALSE);
    }
  }

}
