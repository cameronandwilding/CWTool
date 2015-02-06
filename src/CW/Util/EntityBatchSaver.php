<?php
/**
 * @file
 *
 * Entity batch saver.
 */

namespace CW\Util;

use CW\Model\EntityModel;

/**
 * Class EntityBatchSaver
 * @package CW\Util
 *
 * Saves a batch of updated entities using identity map storage.
 */
class EntityBatchSaver {

  /**
   * @var LocalProcessIdentityMap
   */
  private $entityModelIdentityMap;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $entityModelIdentityMap
   *  Identity map that contains entity models.
   */
  public function __construct(LocalProcessIdentityMap $entityModelIdentityMap) {
    $this->entityModelIdentityMap = $entityModelIdentityMap;
  }

  /**
   * Save all updated entities.
   */
  public function saveAll() {
    /** @var EntityModel $entityModel */
    foreach ($this->entityModelIdentityMap->getAllItems() as $entityModel) {
      if ($entityModel->isDirty()) {
        $entityModel->save();
      }
    }
  }

}
