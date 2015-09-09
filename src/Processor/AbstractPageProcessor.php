<?php
/**
 * @file
 *
 * Abstract page processor.
 */

namespace Drupal\cw_tool\Processor;

/**
 * Class AbstractPageProcessor
 * @package CW\Processor
 *
 * Responsibility is to handle pre/post process hooks for pages.
 */
abstract class AbstractPageProcessor extends AbstractThemeProcessor {

  // Template vars
  const VAR_IS_FRONT_PAGE = 'is_front';
  const VAR_ACTION_LINKS = 'action_links';
  const VAR_TABS = 'tabs';

  /**
   * @return bool
   */
  public function isFrontPage() {
    return (bool) $this->getVar(self::VAR_IS_FRONT_PAGE);
  }
}