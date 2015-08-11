<?php
/**
 * @file
 *  Block handler.
 */

namespace CW\Block;

use CW\Exception\CWException;

/**
 * Class Block
 * @package IntelligentLife\Core\Block
 */
abstract class Block {

  /**
   * Get the block definition for use in hook_block_info().
   *
   * @return array
   */
  public static function getHookBlockInfoArray() {
    return array(static::getDelta() => static::getInfo());
  }

  /**
   * Get the delta to use for the block.
   *
   * @return string
   * @throws \CW\Exception\CWException
   */
  public static function getDelta() {
    throw new CWException('Missing implementation');
  }

  /**
   * Get the info array to use in hook_block_info.
   *
   * @return array
   * @throws \CW\Exception\CWException
   */
  public static function getInfo() {
    throw new CWException('Missing implementation');
  }

  /**
   * Get the render array of the block for hook_block_view.
   *
   * @return array
   */
  public static function getHookBlockViewArray() {
    $block = new static();
    return $block->getRenderArray();
  }

  /**
   * Get the block render array.
   *
   * @return array
   */
  abstract function getRenderArray();
}
