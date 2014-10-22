<?php
/**
 * @file
 */

class CWCollection implements Iterator {

  private $items = array();
  private $keys = array();
  private $idxCurrent = 0;

  public function __construct() {
  }

  public function set($key, $object) {
    if ($this->keyExist($key)) {
      throw new CWToolCacheKeyExistException($key);
    }

    $this->items[$key] = $object;
    $this->keys[] = $key;
  }

  public function get($key) {
    if (!$this->keyExist($key)) {
      return NULL;
    }

    return $this->items[$key];
  }

  public function keyExist($key) {
    return array_key_exists($key, $this->items);
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Return the current element
   * @link http://php.net/manual/en/iterator.current.php
   * @return mixed Can return any type.
   */
  public function current() {
    $key = $this->keys[$this->idxCurrent];
    return $this->items[$key];
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Move forward to next element
   * @link http://php.net/manual/en/iterator.next.php
   * @return void Any returned value is ignored.
   */
  public function next() {
    $this->idxCurrent++;
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Return the key of the current element
   * @link http://php.net/manual/en/iterator.key.php
   * @return mixed scalar on success, or null on failure.
   */
  public function key() {
    return array_key_exists($this->idxCurrent, $this->keys) ? $this->keys[$this->idxCurrent] : NULL;
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Checks if current position is valid
   * @link http://php.net/manual/en/iterator.valid.php
   * @return boolean The return value will be casted to boolean and then evaluated.
   * Returns true on success or false on failure.
   */
  public function valid() {
    return
      !empty($this->keys[$this->idxCurrent]) &&
      !empty($this->items[$this->keys[$this->idxCurrent]]);
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Rewind the Iterator to the first element
   * @link http://php.net/manual/en/iterator.rewind.php
   * @return void Any returned value is ignored.
   */
  public function rewind() {
    $this->idxCurrent = 0;
  }

}
