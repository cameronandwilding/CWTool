<?php
/**
 * @file
 */

namespace CW\Adapter;

/**
 * Interface ConfigurationReaderInterface
 *
 * To read configuration files for various executers.
 *
 * @package CW\Adapter
 */
interface ConfigurationReaderInterface {

  /**
   * @return mixed
   */
  public function read();

}
