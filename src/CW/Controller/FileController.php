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

  /**
   * Gets file uri.
   *
   * @return string|null
   */
  public function getFileURI() {
    return $this->property('uri');
  }

  /**
   * Gets file name.
   *
   * @return string|null
   */
  public function getFileName() {
    return $this->property('filename');
  }

  /**
   * Gets file mime type.
   *
   * @return string|null
   */
  public function getFileMimeType() {
    return $this->property('filemime');
  }
}

/**
 * @}
 */
