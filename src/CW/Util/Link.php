<?php
/**
 * @file
 */

namespace CW\Util;

class Link {

  /**
   * @var null
   */
  private $path;

  /**
   * @var null
   */
  private $text;

  /**
   * @var array
   */
  private $query;

  /**
   * @var null
   */
  private $fragment;

  /**
   * @var bool
   */
  private $absolute;

  /**
   * @param null $path
   * @param null $text
   * @param array $query
   * @param null $fragment
   * @param bool $absolute
   */
  public function __construct($path = NULL, $text = NULL, array $query = array(), $fragment = NULL, $absolute = FALSE) {
    $this->path = $path;
    $this->text = $text;
    $this->query = $query;
    $this->fragment = $fragment;
    $this->absolute = $absolute;
  }

  /**
   * @param $path
   * @param $fragment
   * @return Link
   */
  public static function withPathAndFragment($path, $fragment) {
    return new static($path, NULL, array(), $fragment);
  }

  /**
   * @param $path
   * @param array $query
   * @return Link
   */
  public static function withPathAndQuery($path, array $query) {
    return new static($path, NULL, $query);
  }

  public static function withRequest(Request $request) {
    return new Link(
      $request->getDrupalPath(),
      NULL,
      $request->getGETWithoutDrupalPath()
    );
  }

  public function getDrupalURL() {
    return url($this->path, $this->getDrupalURLOptions());
  }

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
   * @return array
   */
  private function getDrupalURLOptions() {
    return array(
      'query' => $this->query,
      'fragment' => $this->fragment,
      'absolute' => $this->absolute,
    );
  }

}
