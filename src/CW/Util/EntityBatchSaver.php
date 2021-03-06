<?php
/**
 * @file
 *
 * Entity batch saver.
 */

namespace CW\Util;

use CW\Controller\AbstractEntityController;
use Psr\Log\LoggerInterface;

/**
 * Class EntityBatchSaver
 * @package CW\Util
 *
 * Saves a batch of updated entities using identity map storage.
 */
class EntityBatchSaver extends LoggerObject {

  /**
   * @var LocalProcessIdentityMap
   */
  private $entityControllerIdentityMap;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $entityModelIdentityMap
   *  Identity map that contains entity models.
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(LocalProcessIdentityMap $entityModelIdentityMap, LoggerInterface $logger) {
    parent::__construct($logger);

    $this->entityControllerIdentityMap = $entityModelIdentityMap;
  }

  /**
   * Save all updated entities.
   */
  public function saveAll() {
    $allItems = $this->entityControllerIdentityMap->getAllItems();
    /** @var AbstractEntityController $controller */
    foreach ($allItems as $controller) {
      if ($controller->isDirty()) {
        $controller->save();
      }
    }

    $this->logger->info(__METHOD__ . ' Batch save done on {count} node.', array(
      'count' => count($allItems),
    ));
  }

}
