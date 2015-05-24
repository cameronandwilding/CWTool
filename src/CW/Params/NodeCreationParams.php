<?php
/**
 * @file
 *
 * Node creation params.
 */

namespace CW\Params;

use CW\Controller\UserController;

/**
 * Class NodeCreationParams
 * @package CW\Params
 */
class NodeCreationParams extends EntityCreationParams {

  // Node status.
  const NODE_PUBLISHED = 1;
  const NODE_NOT_PUBLISHED = 1;

  /**
   * @var null|string
   */
  private $title;

  /**
   * @var string
   */
  private $type;

  /**
   * @var string
   */
  private $language;

  /**
   * @var int
   */
  private $uid;

  /**
   * @var int
   */
  private $status;

  public function __construct($type, $title = NULL, $language = LANGUAGE_NONE, $uid = UserController::USER_CURRENT, array $extraAttributes = array()) {
    parent::__construct($extraAttributes);

    $this->title = $title ? $title : "untitled";
    $this->type = $type;
    $this->language = $language;
    $this->uid = $uid;
    $this->status = self::NODE_PUBLISHED;
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param mixed $title
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * @return mixed
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * @param string $language
   */
  public function setLanguage($language) {
    $this->language = $language;
  }

  /**
   * @return int
   */
  public function getUid() {
    return $this->uid == UserController::USER_CURRENT ? UserController::currentUID() : $this->uid;
  }

  /**
   * @param int $uid
   */
  public function setUid($uid) {
    $this->uid = $uid;
  }

  /**
   * @return int
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param int $status
   */
  public function setStatus($status) {
    $this->status = $status;
  }

}
