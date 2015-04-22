<?php
/**
 * @file
 *
 * File controller.
 */

namespace CW\Controller;

/**
 * Class FileController
 * @package CW\Controller
 */
class FileController extends AbstractEntityController {

  const ENTITY_TYPE = 'file';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::ENTITY_TYPE;
  }

}
