<?php
/**
 * @file
 *
 * Node model.
 */

/**
 * Class CWToolNodeWrapper
 */
class CWToolNodeWrapper extends CWToolEntityWrapper {

  /**
   * Create or fetch instance by node ID.
   *
   * @param int $nid
   *  Node ID.
   * @return static
   */
  public static function fromNID($nid) {
    return self::getOrCreate('node', $nid, get_called_class());
  }

  /**
   * Create or fetch instance by node object.
   *
   * @param object $node
   *  Node object.
   * @return static
   */
  public static function fromNode($node) {
    $instance = self::fromNID($node->nid);
    $instance->setDrupalObject($node);
    return $instance;
  }

}
