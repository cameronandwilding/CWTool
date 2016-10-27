<?php
/**
 * @file
 *
 * Simple node controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

use CW\Factory\UserControllerFactory;

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

  /**
   * Get the relative path of the content. (Not transformed.)
   * Drupal's url can turn it to the transformed version.
   *
   * @return string
   */
  public function getPath() {
    return 'node/' . $this->getEntityId();
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->property('title');
  }

  /**
   * @return mixed
   */
  public function getCreatedTimestamp() {
    return $this->property('created');
  }

  /**
   * @return mixed
   */
  public function getChangedTimestamp() {
    return $this->property('changed');
  }

  /**
   * Get author UID.
   *
   * @return string|null
   */
  public function getAuthorUID() {
    return $this->property('uid');
  }

  /**
   * @param \CW\Factory\UserControllerFactory $userControllerFactory
   * @return UserController|null
   */
  public function getAuthor(UserControllerFactory $userControllerFactory = NULL) {
    if (!($authorUID = $this->getAuthorUID())) return NULL;

    $userControllerFactory = $userControllerFactory ?: cw_tool_user_factory();
    return $userControllerFactory->initWithId($authorUID);
  }
  
  /**
   * @return string|null
   */
  public function getBody() {
    return $this->fieldValue('field_body');
  }

  /**
   * Render node view.
   *
   * @param string $viewMode
   * @param null $langCode
   * @return string
   */
  public function render($viewMode = 'full', $langCode = NULL) {
    if (!$this->entity()) {
      return NULL;
    }

    $nodeView = node_view($this->entity(), $viewMode, $langCode);
    if (empty($nodeView)) {
      return NULL;
    }

    return render($nodeView);
  }

  /**
   * If exists in database.
   * @return bool
   */
  public function isExist() {
    return (bool) db_query('SELECT COUNT(1) FROM node WHERE nid = :nid', [':nid' => $this->getEntityId()])->fetchField();
  }
  
}

/**
 * @}
 */