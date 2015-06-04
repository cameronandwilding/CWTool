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
use CW\Util\FieldUtil;

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

  /**
   * @return string|NULL
   */
  public function getURI() {
    return $this->property('uri');
  }

  /**
   * @return null|string
   */
  public function getRealPath() {
    if (!($uri = $this->getURI())) {
      return NULL;
    }

    return drupal_realpath($uri);
  }

  /**
   * @return string|NULL
   */
  public function getFileName() {
    return $this->property(FieldUtil::KEY_FILENAME);
  }

}

/**
 * @}
 */
