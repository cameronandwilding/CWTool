<?php
/**
 * @file
 *
 * Cache wrapper.
 */

namespace CW\Util;

use CW\Validator\Validable;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CacheUtil
 * @package CW\Util
 *
 * Cache wrapper is a tiny API to provide cache for functions or code blocks
 * with optional conditions.
 * Example scenario:
 *
 * @code
 * // code
 *
 * $value = heavy_action($arg);
 * $result = heavy_rendering();
 *
 * // code
 * @endcode
 *
 * Let's say we only need caching for non-admin users:
 *
 * @code
 * //code
 *
 * $cacheWrapper = new CacheWrapper(MY_PSR_CACHEPOOL, KEY, function () use ($arg) {
 *   $value = heavy_action($arg);
 *   return heavy_rendering();
 * });
 * $cacheWrapper->addValidation(new IsNonAdminUserValidation());
 * $result = $cacheWrapper->get();
 *
 * // code
 * @endcode
 */
class CacheWrapper {

  // Callback statuses.
  const STATUS_UNCALLED = 0x01;
  const STATUS_INVALID = 0x02;
  const STATUS_CACHE_HIT = 0x04;
  const STATUS_CACHE_MISS = 0x08;

  /**
   * @var CacheItemPoolInterface
   */
  private $cachePool;

  /**
   * @var string
   */
  private $key;

  /**
   * @var callable
   */
  private $callback;

  /**
   * @var Validable[]
   */
  private $validators = array();

  /**
   * @var int
   */
  private $ttl;

  /**
   * @var int
   */
  private $status = self::STATUS_UNCALLED;

  public function __construct(CacheItemPoolInterface $cachePool, $key, callable $callback, $ttl = 0) {
    $this->cachePool = $cachePool;
    $this->key = $key;
    $this->callback = $callback;
    $this->ttl = $ttl;
  }

  public function addValidation(Validable $validable) {
    $this->validators[] = $validable;
    return $this;
  }

  public function get() {
    foreach ($this->validators as $validator) {
      if (!$validator->isValid()) {
        $this->status = self::STATUS_INVALID;
        return call_user_func($this->callback);
      }
    }

    $cacheItem = $this->cachePool->getItem($this->key);
    if ($cacheItem->isHit()) {
      $this->status = self::STATUS_CACHE_HIT;
      return $cacheItem->get();
    }

    $result = call_user_func($this->callback);
    $cacheItem->set($result);
    $cacheItem->setExpiration($this->ttl);
    $this->cachePool->save($cacheItem);
    $this->status = self::STATUS_CACHE_MISS;

    return $result;
  }

  /**
   * @return int
   */
  public function getStatus() {
    return $this->status;
  }

}
