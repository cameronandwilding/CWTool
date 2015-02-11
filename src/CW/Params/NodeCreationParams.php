<?php
/**
 * @file
 */

namespace CW\Params;


use CW\Model\UserModel;

class NodeCreationParams {

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

  /**
   * Fields or node properties.
   *
   * Field example:
   * [
   *  'field_text' => [LANGUAGE_NONE => [['value' => 'some text']],
   * ]
   *
   * @var array
   */
  private $extraAttributes = array();

  public function __construct($title, $type, $extraAttributes = array(), $language = LANGUAGE_NONE, $uid = UserModel::USER_CURRENT) {
    $this->title = $title;
    $this->type = $type;
    $this->extraAttributes = $extraAttributes;
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

  /**
   * @return array
   */
  public function getExtraAttributes() {
    return $this->extraAttributes;
  }

  /**
   * @param array $extraAttributes
   */
  public function setExtraAttributes($extraAttributes) {
    $this->extraAttributes = $extraAttributes;
  }

}
