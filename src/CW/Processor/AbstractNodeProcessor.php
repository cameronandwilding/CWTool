<?php
/**
 * @file
 *
 * Abstract node processor.
 */

namespace CW\Processor;

/**
 * Class AbstractNodeProcessor
 * @package CW\Processor
 *
 * Responsibility is to handle pre/post process hooks for nodes.
 */
abstract class AbstractNodeProcessor extends AbstractThemeProcessor {

  // Template variables.
  const VAR_VIEW_MODE = 'view_mode';
  const VAR_NODE = 'node';

  // Core view modes.
  const VIEW_MODE_NODE_TEASER = 'teaser';
  const VIEW_MODE_NODE_FULL = 'full';

  /**
   * Check if the processor is acting on a given node type.
   *
   * @param string $nodeType
   * @return bool
   */
  public function isNodeType($nodeType) {
    return $this->getVar(self::VAR_NODE)->getType() == $nodeType;
  }

  /**
   * Check if the processor is acting on a given view mode.
   *
   * @param string $viewMode
   *  CoreEntityManager::VIEW_MODE_NODE_*
   * @return bool
   */
  public function isViewMode($viewMode) {
    return $this->getVar(self::VAR_VIEW_MODE) == $viewMode;
  }

  /**
   * Check if the processor is acting on the full view mode.
   *
   * @return bool
   */
  public function isViewModeFull() {
    return $this->isViewMode(self::VIEW_MODE_NODE_FULL);
  }

  /**
   * Check if the processor is acting on the teaser view mode.
   *
   * @return bool
   */
  public function isViewModeTeaser() {
    return $this->isViewMode(self::VIEW_MODE_NODE_TEASER);
  }
}
