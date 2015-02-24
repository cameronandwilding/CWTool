<?php
/**
 * @file
 *
 * Simple node controller.
 */

namespace CW\Controller;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Most basic implementation of node controller.
 */
class NodeController extends AbstractEntityController {

  public static function getClassEntityType() {
    return 'node';
  }

  public function fieldValue($field_name, $key = 'value', $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang][$idx][$key])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang][$idx][$key];
  }

  public function fieldItem($field_name, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang][$idx])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang][$idx];
  }

  public function fieldItems($field_name, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang])) {
      return NULL;
    }
    return $this->entity()->{$field_name}[$lang];
  }

}
