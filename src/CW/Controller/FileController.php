<?php
/**
 * @file
 *
 * File controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

/**
 * Class FileController
 * @package CW\Controller
 */
class FileController extends AbstractEntityController {

  // Entity type.
  const ENTITY_TYPE = 'file';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::ENTITY_TYPE;
  }

}

/**
 * @}
 */
