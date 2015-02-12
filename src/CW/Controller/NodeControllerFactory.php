<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Params\NodeCreationParams;
use Exception;

/**
 * {@inheritdoc}
 */
class NodeControllerFactory extends EntityControllerFactory {

  /**
   * Creates new nodes and wrap them to a node controller.
   * It expects the controller to be descendant of the NodeController.
   *
   * @param \CW\Params\NodeCreationParams $params
   * @return \CW\Controller\NodeController
   * @throws \Exception
   */
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
