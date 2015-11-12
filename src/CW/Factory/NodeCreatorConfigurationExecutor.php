<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\NodeCreationParams;

class NodeCreatorConfigurationExecutor extends CreatorConfigurationExecutor {

  public function setProperties() {
    $props = $this->getParam('properties');
    /** @var NodeCreationParams $param */
    $param = $this->getParam('@param');
    foreach ($props as $prop => $value) {
      $param->setProperty($prop, $value);
    }
  }

  public function setFields() {

  }

  protected function prepare() {
    $this->setProperties();
    $this->setFields();
  }

}
