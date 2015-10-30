<?php
/**
 * @file Drupal entity manager.
 */

namespace CW\Manager;

use CW\Exception\CWException;
use CW\Params\FieldReferenceControllerInfo;
use CW\Params\HookFieldControllerReferenceMapCollector;

/**
 * Class CoreEntityManager
 * @package CW\Manager
 */
class CoreEntityManager {

  // Core view modes.
  const VIEW_MODE_NODE_TEASER = 'teaser';
  const VIEW_MODE_NODE_FULL = 'full';

  // Hook name for defining entity controller info for fields.
  const HOOK_FIELD_CONTROLLER_REFERENCE_MAP = 'cw_tool_field_controller_reference_map';

  /**
   * Looks up the entity reference controller relations to an entity field.
   * Example: for node-article-field_user will refer to YourModule\UserController via it's target_id value key.
   *
   * @param string $fieldName
   * @return FieldReferenceControllerInfo
   * @throws \CW\Exception\CWException
   */
  public static function referenceControllerFactoryLookup($fieldName) {
    $cacheItem = cw_tool_double_cache()->getItem(self::HOOK_FIELD_CONTROLLER_REFERENCE_MAP);
    if (!$cacheItem->isHit()) {
      $collector = new HookFieldControllerReferenceMapCollector();
      module_invoke_all(self::HOOK_FIELD_CONTROLLER_REFERENCE_MAP, $collector);

      $cacheItem->set($collector->getInfo());
      cw_tool_double_cache()->save($cacheItem);
    }

    $info = $cacheItem->get();
    if (empty($info[$fieldName])) {
      throw new CWException('Missing entity controller factory lookup info for field: ' . $fieldName);
    }

    return new FieldReferenceControllerInfo(
      $info[$fieldName][HookFieldControllerReferenceMapCollector::KEY_ENTITY_CONTROLLER_FACTORY_SERVICE],
      $info[$fieldName][HookFieldControllerReferenceMapCollector::KEY_FIELD_VALUE_KEY]
    );
  }

}
