<?php
/**
 * @file
 *
 * Logger object.
 */

namespace CW\Util;

use Psr\Log\LoggerInterface;

/**
 * Class LoggerObject
 * @package CW\Util
 *
 * Basic object that has a logger.
 */
class LoggerObject {

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

}
