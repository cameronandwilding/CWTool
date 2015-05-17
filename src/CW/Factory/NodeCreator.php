<?php
/**
 * @file
 *
 * Node creator.
 */

namespace CW\Factory;

use CW\Controller\AbstractEntityController;
use CW\Params\NodeCreationParams;
use stdClass;

/**
 * Class NodeCreator
 * @package CW\Factory
 */
class NodeCreator implements Creator {

  /**
   * @var \CW\Params\NodeCreationParams
   */
  private $params;

  /**
   * @param \CW\Params\NodeCreationParams $params
   */
  public function __construct(NodeCreationParams $params) {
    $this->params = $params;
  }

  /**
   * Create a node entity.
   *
   * @return AbstractEntityController
   */
  public function create() {
    // Create a new node object.
    $node = new stdClass();
    $node->type = $this->params->getType();
    $node->status = $this->params->getStatus();
    $node->is_new = TRUE;

    // Set defaults.
    node_object_prepare($node);

    // Add our given node information to the node object.
    $node->title = $this->params->getTitle();
    $node->language = $this->params->getLanguage();
    foreach($this->params->getExtraAttributes() as $node_property => $property) {
      $node->$node_property = $property;
    }

    // Node object prepare will attempt to use global $user to add the UID,
    // which will be null if we run through Drush.
    $node->uid = $this->params->getUid();

    // Prepare node for saving. Note that if $node->author is set to a username
    // here it will override the previous value set for UID.
    node_submit($node);

    // Save node and return.
    node_save($node);
    return $node;
  }

}
