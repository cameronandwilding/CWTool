<?php
/**
 * @file
 *
 * Creator.
 */

namespace CW\Factory;

/**
 * Interface Creator
 * @package CW\Factory
 *
 * Class that can create any kind of objects.
 * Used to create entities for entity controller factories.
 */
interface Creator {

  /**
   * Create the specific object and returns it.
   *
   * @return mixed
   */
  public function create();

}
