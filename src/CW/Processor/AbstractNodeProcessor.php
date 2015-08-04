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

  /**
   * Check if the processor is acting on the full view mode.
   *
   * @return bool
   */
  public function isViewModeFull() {
    return $this->getVar(self::VAR_VIEW_MODE) == CoreEntityManager::VIEW_MODE_NODE_FULL;
  }

  /**
   * Check if the processor is acting on the teaser view mode.
   *
   * @return bool
   */
  public function isViewModeTeaser() {
    return $this->getVar(self::VAR_VIEW_MODE) == CoreEntityManager::VIEW_MODE_NODE_TEASER;
  }
}
