<?php
/**
 * @file
 */

namespace CW\Util;

class Request {

  protected $GET;

  protected $SERVER;

  /**
   * @return \CW\Util\Request
   */
  public static function current() {
    static $current;

    if (empty($current)) {
      $current = new Request();
      $current->collectGlobalGetParams();
      $current->collectGlobalServerParams();
    }

    return $current;
  }

  public function collectGlobalGetParams() {
    $this->GET = $_GET;
  }

  public function collectGlobalServerParams() {
    $this->SERVER = $_SERVER;
  }

  public function getDrupalPath() {
    return $this->getGETParam('q');
  }

  public function getTime() {
    return !empty($this->SERVER['REQUEST_TIME']) ? $this->SERVER['REQUEST_TIME'] : time();
  }

  public function getGETParam($key) {
    return isset($this->GET[$key]) ? $this->GET[$key] : NULL;
  }

  public function pageIsCalledFromReferencedDialog() {
    return $this->getGETParam('render') === 'references-dialog';
  }

  /**
   * @return mixed
   */
  public function getGET() {
    return $this->GET;
  }

  public function getGETWithoutDrupalPath() {
    $get = $this->getGET();
    unset($get['q']);
    return $get;
  }

}
