<?php
/**
 * @file
 */

namespace CW\Factory;

interface Creator {

  /**
   * @return object
   */
  public function create();

}