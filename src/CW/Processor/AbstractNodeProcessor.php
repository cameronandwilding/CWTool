<?php
/**
 * @file
 *
 * Abstract node processor.
 */

namespace CW\Processor;

use CW\Manager\CoreEntityManager;

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

  /**
   * Check if the processor is acting on a given node type.
   *
   * @param string $nodeType
   * @return bool
   */
  public function isNodeType($nodeType) {
    return $this->getNodeType() === $nodeType;
  }

  /**
   * Check if the processor is acting on a given view mode.
   *
   * @param string $viewMode
   *  CoreEntityManager::VIEW_MODE_NODE_*
   * @return bool
   */
  public function isViewMode($viewMode) {
    return $this->getViewMode() == $viewMode;
  }

  /**
   * Check if the processor is acting on the full view mode.
   *
   * @return bool
   */
  public function isViewModeFull() {
    return $this->isViewMode(CoreEntityManager::VIEW_MODE_NODE_FULL);
  }

  /**
   * Check if the processor is acting on the teaser view mode.
   *
   * @return bool
   */
  public function isViewModeTeaser() {
    return $this->isViewMode(CoreEntityManager::VIEW_MODE_NODE_TEASER);
  }

  /**
   * @return string
   */
  protected function getViewMode() {
    return $this->getVar(self::VAR_VIEW_MODE);
  }

  /**
   * @return string
   */
  protected function getNodeType() {
    return $this->getWrappedVar()->{self::VAR_NODE}->type->_value();
  }

}
