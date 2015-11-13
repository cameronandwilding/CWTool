
<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\ConfigurationReaderInterface;

class StringConfigurationReader implements ConfigurationReaderInterface {

  /**
   * @var
   */
  private $conf;

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
