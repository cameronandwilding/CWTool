<?php
/**
 * @file
 *
 * Entity batch saver.
 */

namespace CW\Util;

use CW\Model\EntityModel;
use Psr\Log\LoggerInterface;

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
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $entityModelIdentityMap
   *  Identity map that contains entity models.
   */
  public function __construct(LocalProcessIdentityMap $entityModelIdentityMap, LoggerInterface $logger) {
    $this->entityModelIdentityMap = $entityModelIdentityMap;
    $this->logger = $logger;
  }

  /**
   * Save all updated entities.
   */
  public function saveAll() {
    /** @var EntityModel $entityModel */
    $allItems = $this->entityModelIdentityMap->getAllItems();
    foreach ($allItems as $entityModel) {
      if ($entityModel->isDirty()) {
        $entityModel->save();
      }
    }

    $this->logger->info(__METHOD__ . ' Batch save done on {count} node.', array(
      'count' => count($allItems),
    ));
  }

}
