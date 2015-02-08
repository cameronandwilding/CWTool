<?php
/**
 * @file
 */

namespace CW\Util;

class Request {

  protected $GETparams;

  protected $SERVERparams;

  /**
   * @return \CW\Util\Request
   */
  public static function initFromGlobals() {
    $req = new Request();
    $req->collectGlobalGetParams();
    $req->collectGlobalServerParams();
    return $req;
  }

  public function collectGlobalGetParams() {
    $this->GETparams = $_GET;
  }

  public function collectGlobalServerParams() {
    $this->SERVERparams = $_SERVER;
  }

  public function getTime() {
    return !empty($this->SERVERparams['REQUEST_TIME']) ? $this->SERVERparams['REQUEST_TIME'] : time();
  }

  public function getGET($key) {
    return isset($this->GETparams[$key]) ? $this->GETparams[$key] : NULL;
  }

}
