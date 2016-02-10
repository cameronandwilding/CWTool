<?php
/**
 * @file
 *
 * Request.
 */

namespace CW\Util;

/**
 * Class Request
 * @package CW\Util
 *
 * Represents a network request coming to the server.
 */
class Request {

  /**
   * @var array
   */
  protected $GET;

  /**
   * @var array
   */
  protected $SERVER;

  /**
   * @var array
   */
  protected $POST;

  /**
   * @return \CW\Util\Request
   */
  public static function current() {
    static $current;

    if (empty($current)) {
      $current = new static();
      $current->collectGlobalGetParams();
      $current->collectGlobalServerParams();
      $current->collectGlobalPostParams();
    }

    return $current;
  }

  /**
   * Checks if the current request is a POST.
   *
   * @return bool
   */
  public function isPOST() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }

  /**
   * Uses all the options to check if the current request is HTTPS.
   *
   * @return bool
   */
  public function isHTTPS() {
    $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
    $is_https_forwarded = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
    return $is_https || $is_https_forwarded;
  }

  /**
   * Internally gets the GET params.
   */
  protected function collectGlobalGetParams() {
    $this->GET = $_GET;
  }

  /**
   * Internally gets the SERVER params.
   */
  protected function collectGlobalServerParams() {
    $this->SERVER = $_SERVER;
  }

  /**
   * Internally gets the POST params.
   */
  protected function collectGlobalPostParams() {
    $this->POST = $_POST;
  }

  /**
   * @return string
   */
  public function getDrupalPath() {
    return $this->getGETParam('q');
  }

  /**
   * @return int
   */
  public function getTimestamp() {
    return !empty($this->SERVER['REQUEST_TIME']) ? $this->SERVER['REQUEST_TIME'] : time();
  }

  /**
   * @param $key
   * @return null
   */
  public function getGETParam($key) {
    return isset($this->GET[$key]) ? $this->GET[$key] : NULL;
  }

  /**
   * @return bool
   */
  public function pageIsCalledFromEntityReferenceDialog() {
    return $this->getGETParam('render') === 'references-dialog';
  }

  /**
   * @return array
   */
  public function getGETWithDrupalPath() {
    return $this->GET;
  }

  /**
   * @return array
   */
  public function getGET() {
    $get = $this->getGETWithDrupalPath();
    unset($get['q']);
    return $get;
  }

  /**
   * @param string $key
   * @return null|string
   */
  public function getPOSTParam($key) {
    return isset($this->POST[$key]) ? $this->POST[$key] : NULL;
  }

  /**
   * @param string $key
   * @return null|string
   */
  public function getSERVERParam($key) {
    return isset($this->SERVER[$key]) ? $this->SERVER[$key] : NULL;
  }

  /**
   * @param int $index
   * @return null|string
   */
  public function getDrupalPathArgument($index) {
    return arg($index, $this->getDrupalPath());
  }
}
