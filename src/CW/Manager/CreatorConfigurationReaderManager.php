<?php
/**
 * @file
 */

namespace CW\Manager;

use CW\Adapter\ConfigurationReaderInterface;
use CW\Factory\CreatorConfigurationExecutor;

class CreatorConfigurationReaderManager {

  /**
   * @var \CW\Adapter\ConfigurationReaderInterface
   */
  private $configurationReader;

  public function __construct(ConfigurationReaderInterface $configurationReader) {
    $this->configurationReader = $configurationReader;
  }

  public function generate() {
    $products = [];

    $conf = $this->configurationReader->read();
    foreach ($conf['items'] as $id => $item) {
      // @todo make processor to [class, args] so it can be extended -> and possibly make the conf reader util functions a trait.
      $processClass = $item['@executor'];
      /** @var CreatorConfigurationExecutor $processor */
      $processor = new $processClass($item, $products);
      $products[$id] = $processor->create();
    }

    return $products;
  }

}
