<?php
/**
 * @file
 */

namespace CW\Util;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Debug {

  public static function reportEntityException(LoggerInterface $logger, Exception $e, $source, $level = LogLevel::INFO) {
    $logger->log($level, '{source} - entity missing data exception: {exception}', array(
      'source' => $source,
      'exception' => $e->getMessage(),
    ));
  }

}
