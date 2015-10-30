<?php
/**
 * @file
 */

namespace CW\Params;

use CW\Util\FieldUtil;

/**
 * Class HookFieldControllerReferenceMapCollector
 *
 * @package CW\Params
 *
 * Collects info for hook cw_tool_field_controller_reference_map.
 */
class HookFieldControllerReferenceMapCollector {

  // Key names.
  const KEY_ENTITY_CONTROLLER_FACTORY_SERVICE = 0;
  const KEY_FIELD_VALUE_KEY = 1;

  /**
   * @var array
   */
  private $info = [];

  /**
   * @param string $fieldName
   * @param string $entityControllerFactoryServiceName
   * @param string $fieldValueKey
   *  Eg. target_id, nid, uid, etc.
   */
  public function add($fieldName, $entityControllerFactoryServiceName, $fieldValueKey = FieldUtil::KEY_TARGET_ID) {
    $this->info[$fieldName] = [
      self::KEY_ENTITY_CONTROLLER_FACTORY_SERVICE => $entityControllerFactoryServiceName,
      self::KEY_FIELD_VALUE_KEY => $fieldValueKey,
    ];
  }

  /**
   * @return array
   */
  public function getInfo() {
    return $this->info;
  }

}
