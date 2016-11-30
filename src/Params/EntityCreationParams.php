<?php

namespace Drupal\cw_tool\Params;

/**
 * Provides an interface for param objects for entity creator.
 */
interface EntityCreationParams {

  /**
   * Get the values required by the associated entity type's create method.
   *
   * @return array
   */
  public function getValues();

}
