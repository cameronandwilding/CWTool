<?php
/**
 * @file
 */

namespace CW\Controller;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Basic node controller.
 */
class NodeController extends AbstractEntityController {

  /**
   * @return mixed
   */
  public function getAuthor() {
    $uid = $this->entityModel->getDrupalEntityData()->uid;
    $container = cw_tool_get_container();

    return $uid;
  }

}
