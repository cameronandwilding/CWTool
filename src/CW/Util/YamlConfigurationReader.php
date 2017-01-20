<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\ConfigurationReaderInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfigurationReader
 *
 * YAML typed configuration reader. Used for reading *.yml file data to
 * configuration executers.
 *
 * @package CW\Util
 */
class YamlConfigurationReader implements ConfigurationReaderInterface {

  /**
   * @var string
   */
  private $fileName;

  /**
   * YamlConfigurationReader constructor.
   *
   * @param string $fileName
   */
  public function __construct($fileName) {
    $this->fileName = $fileName;
  }

  /**
   * @return mixed
   */
  public function read() {
    return Yaml::parse(file_get_contents($this->fileName));
  }

}
