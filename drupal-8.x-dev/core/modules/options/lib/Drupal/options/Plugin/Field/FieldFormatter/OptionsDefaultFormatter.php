<?php

/**
 * @file
 * Contains \Drupal\options\Plugin\field\formatter\OptionsDefaultFormatter.
 */

namespace Drupal\options\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'list_default' formatter.
 *
 * @FieldFormatter(
 *   id = "list_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "list_integer",
 *     "list_float",
 *     "list_text",
 *     "list_boolean"
 *   }
 * )
 */
class OptionsDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    $entity = $items->getEntity();
    $allowed_values = options_allowed_values($this->fieldDefinition, $entity);

    foreach ($items as $delta => $item) {
      if (isset($allowed_values[$item->value])) {
        $output = field_filter_xss($allowed_values[$item->value]);
      }
      else {
        // If no match was found in allowed values, fall back to the key.
        $output = field_filter_xss($item->value);
      }
      $elements[$delta] = array('#markup' => $output);
    }

    return $elements;
  }

}
