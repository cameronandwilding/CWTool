<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\ConfigurationReaderInterface;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationReader implements ConfigurationReaderInterface {

  /**
   * @var
   */
  private $fileName;

  public function __construct($fileName) {
    $this->fileName = $fileName;
  }

  /**
   * @return mixed
   */
  public function read() {
    return Yaml::parse($this->fileName);
  }

}
