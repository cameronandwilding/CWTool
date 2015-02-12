<?php
/**
 * @file
 */

namespace CW\Params;


use CW\Model\UserModel;

class NodeCreationParams extends EntityCreationParams {

  private $title;

  private $type;

  /**
   * @var string
   */
  private $language;

  /**
   * @var int
   */
  private $uid;

  public function __construct($type, $title = NULL, $language = LANGUAGE_NONE, $uid = UserModel::USER_CURRENT, array $extraAttributes = array()) {
    parent::__construct($extraAttributes);

    $this->title = $title ? $title : "untitled";
    $this->type = $type;
    $this->language = $language;
    $this->uid = $uid;
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
    if ($this->uid == UserModel::USER_CURRENT) {
      global $user;
      return $user->uid;
    }

    return $this->uid;
  }

  /**
   * @param int $uid
   */
  public function setUid($uid) {
    $this->uid = $uid;
  }

}
