<?php
/**
 * @file
 */

namespace CW\Params;

use CW\Factory\EntityControllerFactory;
use CW\Util\FieldUtil;

/**
 * Class FieldReferenceControllerInfo
 *
 * @package CW\Params
 *
 * Param object to keep info about a field's entity reference controller.
 */
class FieldReferenceControllerInfo {

  /**
   * @var string
   */
  private $entityControllerFactoryServiceName;

  /**
   * @var string
   */
  private $fieldKey;

  /**
   * @param string $entityControllerFactoryServiceName
   * @param string $fieldKey
   */
  public function __construct($entityControllerFactoryServiceName, $fieldKey = FieldUtil::KEY_TARGET_ID) {
    $this->entityControllerFactoryServiceName = $entityControllerFactoryServiceName;
    $this->fieldKey = $fieldKey;
  }

  /**
   * @return EntityControllerFactory
   */
  public function getEntityControllerFactory() {
    return cw_tool_get_container()[$this->entityControllerFactoryServiceName];
  }

  /**
   * @return string
   */
  public function getFieldKey() {
    return $this->fieldKey;
  }

}
