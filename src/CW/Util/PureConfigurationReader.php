
<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\ConfigurationReaderInterface;

/**
 * Class PureConfigurationReader
 *
 * @package CW\Util
 *
 * Stores and provides configurations in their final form.
 */
class PureConfigurationReader implements ConfigurationReaderInterface {

  /**
   * @var mixed
   */
  private $conf;

  /**
   * PureConfigurationReader constructor.
   *
   * @param mixed $conf
   */
  public function __construct($conf) {
    $this->conf = $conf;
  }

  /**
   * @return mixed
   */
  public function read() {
    return $this->conf;
  }

}
