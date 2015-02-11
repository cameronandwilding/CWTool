<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Params\NodeCreationParams;
use Exception;

class NodeControllerFactory extends EntityControllerFactory {

  public function initNew(NodeCreationParams $params) {
    $creator = array($this->controllerClass, 'createRaw');
    if (!is_callable($creator)) {
      throw new Exception('');
    }

    $node = call_user_func($creator, $params);
    /** @var NodeController $nodeController */
    $nodeController = $this->initWithId($node->nid);
    $nodeController->getEntityModel()->setDrupalEntityData($node);
    return $nodeController;
  }

}
