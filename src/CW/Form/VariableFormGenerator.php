<?php
/**
 * @file
 */

namespace CW\Form;

use CW\Manager\VariableManager;
use CW\Params\Variable;

class VariableFormGenerator {

  /**
   * @var \CW\Manager\VariableManager
   */
  protected $variableManager;

  public function __construct(VariableManager $variableManager) {
    $this->variableManager = $variableManager;
  }

  public function generateForm($form = array()) {
    foreach ($this->variableManager->getVariables() as $variable) {
      $form_element = array(
        '#title' => $variable->getLabel(),
        '#type' => self::variableTypeToFormElementType($variable->getType()),
        '#default_value' => $variable->getValue(),
      );

      if ($desc = $variable->getDescription()) {
        $form_element['#description'] = $desc;
      }

      $form[$variable->getMachineName()] = $form_element;
    }

    return system_settings_form($form);
  }

  protected static function variableTypeToFormElementType($variableType) {
    switch ($variableType) {
      case Variable::TYPE_LONG_TEXT:
        return 'textarea';

      default:
        return 'textfield';
    }
  }

}
