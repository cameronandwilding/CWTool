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

  /**
   * Get the drupal block render array.
   *
   * @param string $module
   *   Module defining the block.
   * @param string $delta
   *   Delta associated with the block inside the module.
   *
   * @return array
   *   Drupal renderable array for the block.
   */
  public static function getDrupalBlockRenderArray($module, $delta) {
    $block = block_load($module, $delta);
    $block->title = isset($block->title) ? $block->title : '';
    $block->region = isset($block->region) ? $block->region : BLOCK_REGION_NONE;
    return _block_get_renderable_array(_block_render_blocks(array($block)));
  }

}
