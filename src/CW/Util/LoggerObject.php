<?php
/**
 * @file
 */

namespace CW\Util;

use Psr\Log\LoggerInterface;

class LoggerObject {

  /**
   * @var LoggerInterface
   */
  protected $logger;

  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

}
