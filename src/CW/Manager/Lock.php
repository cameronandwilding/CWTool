<?php
/**
 * @file
 */

namespace CW\Manager;

use CW\Util\LoggerObject;
use Psr\Log\LoggerInterface;

/**
 * Class Lock
 *
 * @package CW\Manager
 *
 * Abstraction over Drupal's lock - a simple application level lock.
 * Releasing the lock can be manual or automatic (at object deconstruction time).
 */
class Lock extends LoggerObject {

  const LOCK_WAIT_SECONDS = 90;

  /**
   * @var string
   */
  private $lockTag;

  /**
   * @var bool
   */
  private $isReleased = TRUE;

  /**
   * Lock constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   * @param string $lockTag
   */
  public function __construct(LoggerInterface $logger, $lockTag) {
    parent::__construct($logger);
    $this->lockTag = $lockTag;
  }

  public function __destruct() {
    if (!$this->isReleased) $this->release();
  }

  /**
   * @return bool
   */
  public function lock() {
    if (!($lockGranted = lock_acquire($this->lockTag))) {
      if (
        lock_wait($this->lockTag, self::LOCK_WAIT_SECONDS) ||
        !($lockGranted = lock_acquire($this->lockTag))
      ) {
        $this->logger->error($this->lockTag . ' lock could not be granted.');
        return FALSE;
      }
    }

    $this->isReleased = FALSE;
    return TRUE;
  }

  public function release() {
    lock_release($this->lockTag);
    $this->isReleased = TRUE;
  }

}
