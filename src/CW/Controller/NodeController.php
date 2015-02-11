<?php
/**
 * @file
 *
 * Simple node controller.
 */

namespace CW\Controller;
use stdClass;
use CW\Params\NodeCreationParams;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Most basic implementation of entity controller.
 */
class NodeController extends AbstractEntityController {

  public static function createRaw(NodeCreationParams $params) {
    // Create a new node object.
    $node = new stdClass();
    $node->type = $params->getType();
    $node->is_new = TRUE;

    // Set defaults.
    node_object_prepare($node);

    // Add our given node information to the node object.
    $node->title = $params->getTitle();
    $node->language = $params->getLanguage();
    foreach($params->getExtraAttributes() as $node_property => $property) {
      $node->$node_property = $property;
    }

    // Node object prepare will attempt to use global $user to add the UID,
    // which will be null if we run through Drush.
    $node->uid = $params->getUid();

    // Prepare node for saving. Note that if $node->author is set to a username
    // here it will override the previous value set for UID.
    node_submit($node);

    // Save node and return.
    node_save($node);
    return $node;
  }

}
