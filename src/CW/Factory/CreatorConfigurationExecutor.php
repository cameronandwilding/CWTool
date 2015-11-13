<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Exception\CWException;
use CW\Util\AssocArray;

abstract class CreatorConfigurationExecutor {

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

  public function __construct(array $conf, array $products) {
    $this->conf = $conf;
    $this->paramCache = new AssocArray();
    $this->products = $products;
  }

  /**
   * @param mixed $arg
   * @return array
   */
  private function resolveConfArgs($arg) {
    if (!is_array($arg)) {
      return $this->nameIsReference($arg) ? $this->getConfParam($arg) : $arg;
    }

    return array_map([__CLASS__, __FUNCTION__], $arg);
  }

  protected function resolveParamArgs($param) {
    if (!is_array($param)) {
      return $this->nameIsReference($param) ? $this->resolveParamReference($param) : $param;
    }

    return array_map([__CLASS__, __FUNCTION__], $param);
  }

  private function resolveParamReference($ref) {
    $parts = explode('.', $ref);
    // Remove '@' sign.
    $productID = substr(array_shift($parts), 1);

    if (!($currentProduct = $this->products[$productID])) {
      throw new CWException('Missing product from reference: ' . $ref);
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
   * @param $name
   * @param null $default
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  protected function getConfParam($name, $default = NULL) {
    if ($this->paramCache->has($name)) {
      return $this->paramCache->{$name};
    }

    if (!array_key_exists($name, $this->conf)) {
      return $default;
    }

    $info = $this->conf[$name];

    $param = NULL;
    if (!is_array($info)) {
      $param = $this->nameIsReference($info) ? $this->getConfParam($info) : $info;
    }
    elseif (!empty($info['class'])) {
      $class = $info['class'];
      if (!class_exists($class)) {
        throw new CWException('Missing class: ' . $class);
      }

      $args = !empty($info['args']) ? $info['args'] : [];
      $processedArgs = $this->resolveConfArgs($args);
      $reflClass = new \ReflectionClass($class);
      $param = $reflClass->newInstanceArgs($processedArgs);
    }
    else {
      $param = $info;
    }

    return $this->paramCache->{$name} = $param;
  }

  private function nameIsReference($name) {
    return strpos($name, '@') === 0;
  }

  abstract protected function prepare();

  /**
   * @return object
   */
  public function create() {
    $this->prepare();
    $nodeCreator = $this->getConfParam('@creator');
    return $nodeCreator->create();
  }

}
