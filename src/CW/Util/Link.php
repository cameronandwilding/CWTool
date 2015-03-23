<?php
/**
 * @file
 *
 * Link.
 */

namespace CW\Util;

/**
 * Class Link
 * @package CW\Util
 *
 * Link to generate links and being able to pass them around when only partially
 * completed.
 */
class Link {

  /**
   * @var string
   */
  private $path;

  /**
   * @var string
   */
  private $text;

  /**
   * @var array
   */
  private $query;

  /**
   * @var string
   */
  private $fragment;

  /**
   * @var bool
   */
  private $absolute;

  /**
   * @var bool
   */
  private $isFormatHtml = FALSE;

  /**
   * @var array
   */
  private $attributes;

  /**
   * @param null $path
   * @param null $text
   * @param array $query
   * @param null $fragment
   * @param bool $absolute
   */
  public function __construct($path = NULL, $text = NULL, array $query = array(), $fragment = NULL, $absolute = FALSE, $attributes = FALSE) {
    $this->path = $path;
    $this->text = $text;
    $this->query = $query;
    $this->fragment = $fragment;
    $this->absolute = $absolute;
    $this->attributes = $attributes;
  }

  /**
   * Factory.
   *
   * @param $path
   * @param $fragment
   * @return Link
   */
  public static function withPathAndFragment($path, $fragment) {
    return new static($path, NULL, array(), $fragment);
  }

  /**
   * Factory.
   *
   * @param $path
   * @param array $query
   * @return Link
   */
  public static function withPathAndQuery($path, array $query) {
    return new static($path, NULL, $query);
  }

  /**
   * Factory.
   *
   * @param \CW\Util\Request $request
   * @return \CW\Util\Link
   */
  public static function withRequest(Request $request) {
    return new Link(
      $request->getDrupalPath(),
      NULL,
      $request->getGET()
    );
  }

  /**
   * @return string
   */
  public function getDrupalURL() {
    return url($this->path, $this->getDrupalURLOptions());
  }

  /**
   * @return string
   */
  public function getDrupalLink() {
    return l($this->text, $this->path, $this->getDrupalURLOptions());
  }

  /**
   * @param null $path
   * @return $this
   */
  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  /**
   * @param null $text
   * @return $this
   */
  public function setText($text) {
    $this->text = $text;
    return $this;
  }

  /**
   * @param array $query
   * @return $this
   */
  public function setQuery($query) {
    $this->query = $query;
    return $this;
  }

  /**
   * @param $key
   * @param $value
   * @return Link
   */
  public function setQueryParam($key, $value) {
    $this->query[$key] = $value;
    return $this;
  }

  /**
   * @param null $fragment
   * @return $this
   */
  public function setFragment($fragment) {
    $this->fragment = $fragment;
    return $this;
  }

  /**
   * @param boolean $absolute
   * @return $this
   */
  public function setAbsolute($absolute) {
    $this->absolute = $absolute;
    return $this;
  }

  /**
   * @param boolean $html
   * @return $this
   */
  public function setOutputFormatToHtml() {
    $this->isFormatHtml = TRUE;
    return $this;
  }

  /**
   * @param array $attribute
   * @return $this
   */
  public function setAttributes($attribute) {
    $this->attributes = $attribute;
    return $this;
  }

  /**
   * @return array
   */
  private function getDrupalURLOptions() {
    return array(
      'query' => $this->query,
      'fragment' => $this->fragment,
      'absolute' => $this->absolute,
      'html' => $this->isFormatHtml,
      'attributes' => $this->attributes
    );
  }

}
