<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Adapter\UtilityCollectionInterface;
use CW\Exception\CWException;
use CW\Util\AssocArray;

abstract class CreatorConfigurationExecutor {

  const MARKER_CONFIGURATION = '@';
  const MARKER_PRODUCT = '$';
  const MARKER_FUNCTION = '%';

  /**
   * @var array
   */
  private $conf;

  /**
   * @var AssocArray
   */
  private $paramCache;

  /**
   * @var array
   */
  private $products;

  /**
   * @var \CW\Adapter\UtilityCollectionInterface
   */
  private $utilityCollection;

  public function __construct(array $conf, array $products, UtilityCollectionInterface $utilityCollection) {
    $this->conf = $conf;
    $this->paramCache = new AssocArray();
    $this->products = $products;
    $this->utilityCollection = $utilityCollection;
  }

  public function resolveValue($confValue) {
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
   * @param $name
   * @param null $default
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  protected function getConfiguration($name, $default = NULL) {
    // Caching is crucial so we do not instantiate classes multiple times.
    if ($this->paramCache->has($name)) {
      return $this->paramCache->{$name};
    }

    if (!array_key_exists($name, $this->conf)) {
      return $default;
    }

    return $this->paramCache->{$name} = $this->resolveValue($this->conf[$name]);
  }

  private function isConfigurationReference($name) {
    return $this->isFirstChar($name, self::MARKER_CONFIGURATION);
  }

  private function isProductReference($name) {
    return $this->isFirstChar($name, self::MARKER_PRODUCT);
  }

  private function isClass($def) {
    return is_array($def) && !empty($def['class']);
  }

  private function isFunction($name) {
    return $this->isFirstChar($name, self::MARKER_FUNCTION);
  }

  private function isFirstChar($string, $char) {
    return strpos($string, $char) === 0;
  }

  abstract protected function prepare();

  /**
   * @return object
   * @throws \CW\Exception\CWException
   */
  public function create() {
    $this->prepare();
    $nodeCreator = $this->getConfiguration('@creator');
    if (!is_subclass_of($nodeCreator, 'CW\Factory\Creator')) {
      throw new CWException('@creator is not implementing the CW\\Factory\\Creator interface.');
    }

    return $nodeCreator->create();
  }

}
