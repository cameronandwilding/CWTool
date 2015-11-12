<?php
/**
 * @file
 */

namespace CW\Factory;

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

  public function __construct(array $conf) {
    $this->conf = $conf;
    $this->paramCache = new AssocArray();
  }

  /**
   * @param array $args
   * @return array
   */
  private function resolveArgs(array $args) {
    $processed = [];
    foreach ($args as $arg) {
      $processed[] = $this->nameIsReference($arg) ? $this->getParam($arg) : $processed[] = $arg;
    }

    return $processed;
  }

  /**
   * @param $name
   * @return mixed
   * @throws \CW\Exception\CWException
   */
  protected function getParam($name) {
    if ($this->paramCache->has($name)) {
      return $this->paramCache->{$name};
    }

    $info = $this->conf[$name];

    $param = NULL;
    if (!is_array($info)) {
      $param = $this->nameIsReference($info) ? $this->getParam($info) : $info;
    }
    elseif (!empty($info['class']) && !empty($info['args'])) {
      $class = $info['class'];
      $args = $info['args'];
      $processedArgs = $this->resolveArgs($args);
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
    $nodeCreator = $this->getParam('@creator');
    return $nodeCreator->create();
  }

}
