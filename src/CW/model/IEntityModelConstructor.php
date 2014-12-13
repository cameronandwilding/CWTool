<?php
/**
 * @file
 */

namespace CW\Model;

/**
 * Interface IEntityModelConstructor
 * @package CW\Model
 *
 * Interface for entity constructors.
 */
interface IEntityModelConstructor {

  /**
   * Constructor.
   *
   * @param string $entity_type
   *  Entity type name.
   * @param string $entity_id
   *  Entity ID.
   */
  public function __construct($entity_type, $entity_id);

}
