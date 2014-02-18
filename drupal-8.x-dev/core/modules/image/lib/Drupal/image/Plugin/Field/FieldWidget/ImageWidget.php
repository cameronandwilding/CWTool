<?php

/**
 * @file
 * Contains \Drupal\image\Plugin\Field\FieldWidget\ImageWidget.
 */

namespace Drupal\image\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;

/**
 * Plugin implementation of the 'image_image' widget.
 *
 * @FieldWidget(
 *   id = "image_image",
 *   label = @Translation("Image"),
 *   field_types = {
 *     "image"
 *   },
 *   settings = {
 *     "progress_indicator" = "throbber",
 *     "preview_image_style" = "thumbnail",
 *   }
 * )
 */
class ImageWidget extends FileWidget {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['preview_image_style'] = array(
      '#title' => t('Preview image style'),
      '#type' => 'select',
      '#options' => image_style_options(FALSE),
      '#empty_option' => '<' . t('no preview') . '>',
      '#default_value' => $this->getSetting('preview_image_style'),
      '#description' => t('The preview image will be shown while editing the content.'),
      '#weight' => 15,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('preview_image_style');
    if (isset($image_styles[$image_style_setting])) {
      $preview_image_style = t('Preview image style: @style', array('@style' => $image_styles[$image_style_setting]));
    }
    else {
      $preview_image_style = t('Original image');
    }

    array_unshift($summary, $preview_image_style);

    return $summary;
  }

  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, array &$form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $cardinality = $this->fieldDefinition->getCardinality();
    $file_upload_help = array(
      '#theme' => 'file_upload_help',
      '#description' => '',
      '#upload_validators' => $elements[0]['#upload_validators'],
      '#cardinality' => $cardinality,
    );
    if ($cardinality == 1) {
      // If there's only one field, return it as delta 0.
      if (empty($elements[0]['#default_value']['fids'])) {
        $file_upload_help['#description'] = field_filter_xss($this->fieldDefinition->getDescription());
        $elements[0]['#description'] = drupal_render($file_upload_help);
      }
    }
    else {
      $elements['#file_upload_description'] = $file_upload_help;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $field_settings = $this->getFieldSettings();

    // Add upload resolution validation.
    if ($field_settings['max_resolution'] || $field_settings['min_resolution']) {
      $element['#upload_validators']['file_validate_image_resolution'] = array($field_settings['max_resolution'], $field_settings['min_resolution']);
    }

    // If not using custom extension validation, ensure this is an image.
    $supported_extensions = array('png', 'gif', 'jpg', 'jpeg');
    $extensions = isset($element['#upload_validators']['file_validate_extensions'][0]) ? $element['#upload_validators']['file_validate_extensions'][0] : implode(' ', $supported_extensions);
    $extensions = array_intersect(explode(' ', $extensions), $supported_extensions);
    $element['#upload_validators']['file_validate_extensions'][0] = implode(' ', $extensions);

    // Add all extra functionality provided by the image widget.
    $element['#process'][] = array(get_class($this), 'process');
    // Add properties needed by process() method.
    $element['#preview_image_style'] = $this->getSetting('preview_image_style');
    $element['#title_field'] = $field_settings['title_field'];
    $element['#title_field_required'] = $field_settings['title_field_required'];
    $element['#alt_field'] = $field_settings['alt_field'];
    $element['#alt_field_required'] = $field_settings['alt_field_required'];

    return $element;
  }

  /**
   * Form API callback: Processes a image_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, &$form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    $element['#theme'] = 'image_widget';
    $element['#attached']['css'][] = drupal_get_path('module', 'image') . '/css/image.theme.css';

    // Add the image preview.
    if (!empty($element['#files']) && $element['#preview_image_style']) {
      $file = reset($element['#files']);
      $variables = array(
        'style_name' => $element['#preview_image_style'],
        'uri' => $file->getFileUri(),
      );

      // Determine image dimensions.
      if (isset($element['#value']['width']) && isset($element['#value']['height'])) {
        $variables['width'] = $element['#value']['width'];
        $variables['height'] = $element['#value']['height'];
      }
      else {
        $image = \Drupal::service('image.factory')->get($file->getFileUri());
        if ($image->getExtension()) {
          $variables['width'] = $image->getWidth();
          $variables['height'] = $image->getHeight();
        }
        else {
          $variables['width'] = $variables['height'] = NULL;
        }
      }

      $element['preview'] = array(
        '#theme' => 'image_style',
        '#width' => $variables['width'],
        '#height' => $variables['height'],
        '#style_name' => $variables['style_name'],
        '#uri' => $variables['uri'],
      );

      // Store the dimensions in the form so the file doesn't have to be
      // accessed again. This is important for remote files.
      $element['width'] = array(
        '#type' => 'hidden',
        '#value' => $variables['width'],
      );
      $element['height'] = array(
        '#type' => 'hidden',
        '#value' => $variables['height'],
      );
    }

    // Add the additional alt and title fields.
    $element['alt'] = array(
      '#title' => t('Alternate text'),
      '#type' => 'textfield',
      '#default_value' => isset($item['alt']) ? $item['alt'] : '',
      '#description' => t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      // @see http://www.gawds.org/show.php?contentid=28
      '#maxlength' => 512,
      '#weight' => -2,
      '#access' => (bool) $item['fids'] && $element['#alt_field'],
      '#element_validate' => $element['#alt_field_required'] == 1 ? array(array(get_called_class(), 'validateRequiredFields')) : array(),
    );
    $element['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => isset($item['title']) ? $item['title'] : '',
      '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#maxlength' => 1024,
      '#weight' => -1,
      '#access' => (bool) $item['fids'] && $element['#title_field'],
      '#element_validate' => $element['#title_field_required'] == 1 ? array(array(get_called_class(), 'validateRequiredFields')) : array(),
    );

    return $element;
  }

  /**
   * Validate callback for alt and title field, if the user wants them required.
   *
   * This is separated in a validate function instead of a #required flag to
   * avoid being validated on the process callback.
   */
  public static function validateRequiredFields($element, &$form_state) {
    // Only do validation if the function is triggered from other places than
    // the image process form.
    if (!in_array('file_managed_file_submit', $form_state['triggering_element']['#submit'])) {
      // If the image is not there, we do not check for empty values.
      $parents = $element['#parents'];
      $field = array_pop($parents);
      $image_field = NestedArray::getValue($form_state['input'], $parents);
      // We check for the array key, so that it can be NULL (like if the user
      // submits the form without using the "upload" button).
      if (!array_key_exists($field, $image_field)) {
        return;
      }
      // Check if field is left empty.
      elseif (empty($image_field[$field])) {
        \Drupal::formBuilder()->setError($element, $form_state, t('The field !title is required', array('!title' => $element['#title'])));
        return;
      }
    }
  }


}
