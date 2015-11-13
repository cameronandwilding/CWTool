<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\EntityCreationParams;

interface CreatorParamFactory {

  /**
   * @return EntityCreationParams
   */
  public function get();

}
