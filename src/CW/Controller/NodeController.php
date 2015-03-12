<?php
/**
 * @file
 *
 * Simple node controller.
 */

namespace CW\Controller;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Most basic implementation of node controller.
 */
class NodeController extends AbstractEntityController {

  // Entity type.
  const TYPE_NODE = 'node';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::TYPE_NODE;
  }

  /**
   * @return bool
   */
  public function isPublished() {
    return $this->property('status') == NODE_PUBLISHED;
  }

}
