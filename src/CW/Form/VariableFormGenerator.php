<?php
/**
 * @file
 *
 * Variable form generator.
 */

namespace CW\Form;

use CW\Manager\VariableManager;
use CW\Params\Variable;

/**
 * Class VariableFormGenerator
 * @package CW\Form
 *
 * This is a helper class to create forms for variables stored in a variable
 * manager instance.
 * The generated form is a system settings form.
 */
class VariableFormGenerator {

  /**
   * @var \CW\Manager\VariableManager
   */
  protected $variableManager;

  /**
   * @param \CW\Manager\VariableManager $variableManager
   */
  public function __construct(VariableManager $variableManager) {
    $this->variableManager = $variableManager;
  }

  /**
   * @param array $form
   *  Form array from the original form callback, optional.
   * @return array
   */
  public function generateForm($form = array()) {
    foreach ($this->variableManager->getVariables() as $variable) {
      $form_element = array(
        '#title' => $variable->getLabel(),
        '#type' => self::getFormElementTypeOfVariableType($variable->getType()),
        '#default_value' => $variable->getValue(),
      );

      if ($desc = $variable->getDescription()) {
        $form_element['#description'] = $desc;
      }

      $form[$variable->getMachineName()] = $form_element;
    }

    return system_settings_form($form);
  }

  /**
   * @param string $variableType
   * @return string
   */
  protected static function getFormElementTypeOfVariableType($variableType) {
    switch ($variableType) {
      case Variable::TYPE_LONG_TEXT:
        return 'textarea';

      case Variable::TYPE_FORMATTED_TEXT:
        return 'text_format';

      default:
        return 'textfield';
    }
  }

}
