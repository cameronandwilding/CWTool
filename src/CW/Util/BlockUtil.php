<?php
/**
 * @file
 *
 * Block utility functions.
 */

namespace CW\Util;

/**
 * Class BlockUtil
 * @package CW\Util
 */
class BlockUtil {

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
  public static function getRenderArray($module, $delta) {
    $block = block_load($module, $delta);
    $block->title = isset($block->title) ? $block->title : '';
    $block->region = isset($block->region) ? $block->region : BLOCK_REGION_NONE;
    return _block_get_renderable_array(_block_render_blocks(array($block)));
  }
}