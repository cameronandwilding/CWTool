<?php
/**
 * @file
 *
 * Simple parameter object for service containers.
 */

namespace CW\Util;

/**
 * Class ServiceContainerDefinitionCollection
 * @package CW\Util
 */
class ServiceContainerDefinitionCollection {

  /**
   * @var array
   */
  protected $configs = array();

  /**
   * Add configuration.
   * File name expected to be the standard: services.yml.
   *
   * @param string $path
   */
  public function add($path) {
    $this->configs[] = $path;
  }

  /**
   * Get paths.
   *
   * @return string[]
   */
  public function getConfigs() {
    return $this->configs;
  }

}
