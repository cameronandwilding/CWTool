<?php
/**
 * @file
 */

namespace CW\Manager;

use CW\Adapter\ConfigurationReaderInterface;
use CW\Adapter\UtilityCollectionInterface;
use CW\Exception\CWException;
use CW\Factory\CreatorConfigurationExecutor;

/**
 * Class CreatorConfigurationReaderManager
 *
 * @package CW\Manager
 *
 * Manages the complete process of object creation feeded by configuration.
 * The configuration can describe multiple objects (most commonly entities) to
 * manufacture.
 * The configuration format is strict on the main level but flexible on the
 * item definition level.
 *
 * Minimum requirement:
 *
 * {@code}
 *  items:
 *    ID0:
 *      executor: CLASS_NAME
 *      creator:
 *        class: CLASS_NAME
 * {@endcode}
 *
 * Specs:
 * - "items": contain a list of object definitions
 * - item has a key which is used as ID and future reference
 * - item has to define an "executor" a subclass of CW\Factory\CreatorConfigurationExecutor
 * - item has to define a "creator" which is subclass of CW\Factory\Creator
 * - "creator" and any class definition has a "class" (required) and "args" (optional array)
 * - values can be referenced by @ prefix (example below)
 * - products can be referenced by $ prefix and accessing it's keys/properties works with a . separation (example below)
 * - arbitrary function execution (hosted by the injected utility collection) can by done with % prefix (example below)
 *
 * Executor classes are encouraged to subclass. The subclass is responsible for
 * handling the specific part of the configuration (eg fields definition for entities).
 *
 * Basic user entity creation example:
 *
 * {@code}
 *  items:
 *    ID0: ...
 *    ID1:
 *      executor: CW\Factory\EntityCreatorConfigurationExecutor
 *      param:
 *        class: CW\Params\UserCreationParams
 *        args: ["Joe", [3: administrator]]
 *      creator:
 *        class: CW\Factory\UserCreator
 *        args: [@param]
 *      properties:
 *        mail: joe@example.com
 *        pass: $ID0.title
 *      fields:
 *        field_first_name: %randomString(32)
 *        field_photo:
 *          uri: public://me.jpg
 *    ID2: ...
 * {@endcode}
 */
class CreatorConfigurationExecutorManager {

  /**
   * Provides the configuration.
   *
   * @var \CW\Adapter\ConfigurationReaderInterface
   */
  private $configurationReader;

  /**
   * Collection of function for using by the configuration.
   *
   * @var \CW\Adapter\UtilityCollectionInterface
   */
  private $utilityCollection;

  /**
   * CreatorConfigurationExecutorManager constructor.
   *
   * @param \CW\Adapter\ConfigurationReaderInterface $configurationReader
   * @param \CW\Adapter\UtilityCollectionInterface $utilityCollection
   */
  public function __construct(ConfigurationReaderInterface $configurationReader, UtilityCollectionInterface $utilityCollection) {
    $this->configurationReader = $configurationReader;
    $this->utilityCollection = $utilityCollection;
  }

  /**
   * @return object[]
   * @throws \CW\Exception\CWException
   */
  public function generate() {
    $products = [];

    $conf = $this->configurationReader->read();
    foreach ($conf['items'] as $id => $item) {
      $executorClass = $item['executor'];
      if (!is_subclass_of($executorClass, 'CW\Factory\CreatorConfigurationExecutor')) {
        throw new CWException('Executor class (' . $executorClass . ') needs to be subclass of CW\Factory\CreatorConfigurationExecutor.');
      }

      /** @var CreatorConfigurationExecutor $executor */
      $executor = new $executorClass($item, $products, $this->utilityCollection);
      $products[$id] = $executor->create();
    }

    return $products;
  }

}
