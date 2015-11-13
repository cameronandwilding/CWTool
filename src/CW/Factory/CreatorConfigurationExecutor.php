<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Adapter\UtilityCollectionInterface;
use CW\Exception\CWException;
use CW\Util\AssocArray;

/**
 * Class CreatorConfigurationExecutor
 *
 * @package CW\Factory
 *
 * Object factory executor for CW\Factory\Creator creators.
 * This is the base class for executors. It is required to create specific executors,
 * because the process of object creation and the configuration needed for it
 * can be hugely different.
 * Basically anything can be created that can use a Creator class.
 */
abstract class CreatorConfigurationExecutor {

  // Special value markers.
  const MARKER_CONFIGURATION = '@';
  const MARKER_PRODUCT = '$';
  const MARKER_FUNCTION = '%';

  /**
   * Configuration for the product item.
   *
   * @var array
   */
  private $conf;

  /**
   * Cache for high level generated configuration values (classes mostly).
   *
   * @var AssocArray
   */
  private $paramCache;

  /**
   * All generated objects created so far in the process.
   * Used for referencing back to already created product properties.
   *
   * @var array
   */
  private $products;

  /**
   * To provide a set of functions to call from the configuration to set default values.
   *
   * @var \CW\Adapter\UtilityCollectionInterface
   */
  private $utilityCollection;

  /**
   * CreatorConfigurationExecutor constructor.
   *
   * @param array $conf
   * @param object[] $products
   * @param \CW\Adapter\UtilityCollectionInterface $utilityCollection
   */
  public function __construct(array $conf, array $products, UtilityCollectionInterface $utilityCollection) {
    $this->conf = $conf;
    $this->paramCache = new AssocArray();
    $this->products = $products;
    $this->utilityCollection = $utilityCollection;
  }

  /**
   * Resolve a configuration value - being a value, reference or function.
   *
   * @param mixed $confValue
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  protected function resolveValue($confValue) {
    $param = NULL;
    if (!is_array($confValue)) {
      if ($this->isConfigurationReference($confValue)) {
        $param = $this->getConfiguration($confValue);
      }
      elseif ($this->isProductReference($confValue)) {
        $param = $this->resolveProductProperty($confValue);
      }
      elseif ($this->isFunction($confValue)) {
        $param = $this->resolveFunction($confValue);
      }
      else {
        $param = $confValue;
      }
    }
    elseif ($this->isClass($confValue)) {
      $param = $this->resolveClass($confValue);
    }
    else {
      $param = array_map([__CLASS__, __FUNCTION__], $confValue);
    }

    return $param;
  }

  /**
   * @param string $confValue
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  private function resolveProductProperty($confValue) {
    $parts = explode('.', $confValue);
    // Remove reference marker sign.
    $productID = substr(array_shift($parts), 1);

    if (!($currentProduct = $this->products[$productID])) {
      throw new CWException('Missing product from reference: ' . $confValue);
    }

    while ($propName = array_shift($parts)) {
      if (is_array($currentProduct)) {
        $currentProduct = @$currentProduct[$propName];
      }
      elseif (is_object($currentProduct)) {
        $currentProduct = @$currentProduct->{$propName};
      }
      else {
        throw new CWException('Unable to retrieve property ' . $propName . ' from non-array / non-object element.');
      }
    }

    return $currentProduct;
  }

  /**
   * @param array $confValue
   * @return object
   * @throws \CW\Exception\CWException
   */
  private function resolveClass($confValue) {
    $class = $confValue['class'];
    if (!class_exists($class)) {
      throw new CWException('Missing class: ' . $class);
    }

    $args = !empty($confValue['args']) ? $confValue['args'] : [];
    $processedArgs = $this->resolveValue($args);
    $reflClass = new \ReflectionClass($class);
    return $reflClass->newInstanceArgs($processedArgs);
  }

  /**
   * @param string $confValue
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  private function resolveFunction($confValue) {
    $matches = NULL;
    $confValue = substr($confValue, 1);
    preg_match('/^(?P<fn>[^\(]+)($|\((?<args>.*)\))/', $confValue, $matches);

    $funcName = $matches['fn'];
    if (!method_exists($this->utilityCollection, $funcName)) {
      throw new CWException('Method: ' . $funcName . ' does not exist on class: ' . get_class($this->utilityCollection));
    }

    $args = !empty($matches['args']) ? explode(',', $matches['args']) : [];
    $args = $this->resolveValue($args);

    return call_user_func_array([$this->utilityCollection, $funcName], $args);
  }

  /**
   * @param string $name
   * @param mixed $default
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  protected function getConfiguration($name, $default = NULL) {
    // Remove marker char.
    $name = substr($name, 1);

    // Caching is crucial so we do not instantiate classes multiple times.
    if ($this->paramCache->has($name)) {
      return $this->paramCache->{$name};
    }

    if (!array_key_exists($name, $this->conf)) {
      return $default;
    }

    return $this->paramCache->{$name} = $this->resolveValue($this->conf[$name]);
  }

  /**
   * @param string $name
   * @return bool
   */
  private function isConfigurationReference($name) {
    return $this->isFirstChar($name, self::MARKER_CONFIGURATION);
  }

  /**
   * @param string $name
   * @return bool
   */
  private function isProductReference($name) {
    return $this->isFirstChar($name, self::MARKER_PRODUCT);
  }

  /**
   * @param array $def
   * @return bool
   */
  private function isClass($def) {
    return is_array($def) && !empty($def['class']);
  }

  /**
   * @param string $name
   * @return bool
   */
  private function isFunction($name) {
    return $this->isFirstChar($name, self::MARKER_FUNCTION);
  }

  /**
   * @param string $string
   * @param string $char
   * @return bool
   */
  private function isFirstChar($string, $char) {
    return strpos($string, $char) === 0;
  }

  /**
   * Prepare configuration specifically to the desired type.
   */
  abstract protected function prepare();

  /**
   * @return object
   * @throws \CW\Exception\CWException
   */
  public function create() {
    $this->prepare();
    $nodeCreator = $this->getConfiguration('@creator');
    if (!is_subclass_of($nodeCreator, 'CW\Factory\Creator')) {
      throw new CWException('creator is not implementing the CW\\Factory\\Creator interface.');
    }

    return $nodeCreator->create();
  }

}
