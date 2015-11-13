<?php
/**
 * @file
 */

namespace CW\Manager;

use CW\Adapter\ConfigurationReaderInterface;
use CW\Adapter\UtilityCollectionInterface;
use CW\Factory\CreatorConfigurationExecutor;

class CreatorConfigurationReaderManager {

  /**
   * @var \CW\Adapter\ConfigurationReaderInterface
   */
  private $configurationReader;

  /**
   * @var \CW\Adapter\UtilityCollectionInterface
   */
  private $utilityCollection;

  public function __construct(ConfigurationReaderInterface $configurationReader, UtilityCollectionInterface $utilityCollection) {
    $this->configurationReader = $configurationReader;
    $this->utilityCollection = $utilityCollection;
  }

  public function generate() {
    $products = [];

    $conf = $this->configurationReader->read();
    foreach ($conf['items'] as $id => $item) {
      // @todo make executor to [class, args] so it can be extended -> and possibly make the conf reader util functions a trait.
      $processClass = $item['@executor'];
      /** @var CreatorConfigurationExecutor $executor */
      $executor = new $processClass($item, $products, $this->utilityCollection);
      $products[$id] = $executor->create();
    }

    return $products;
  }

}
