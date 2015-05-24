<?php
/**
 * @file
 *
 * Model behavior.
 */

namespace CW\Model;

/**
 * Interface Model
 * @package CW\Model
 *
 * Defines a basic model that can be deleted and saved.
 */
interface Model {

  /**
   * Save object.
   */
  public function save();

  /**
   * Delete object.
   */
  public function delete();

}
