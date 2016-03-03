<?php
/**
 * @file
 */

namespace CW\Factory;

interface EntityControllerFactoryInterface {

  /**
   * Factory method. This a MUST initializer for entity controllers.
   * This method checks all items in cache (identity map) and load if it's
   * possible.
   *
   * @param mixed $entity_id
   *  In case it's missing (NULL), always provide a $cacheKey.
   * @param null $cacheKey
   *  Only use cache key when entity is missing or not unique.
   * @return \CW\Controller\AbstractEntityController
   * @throws \CW\Exception\IdentityMapException
   * @throws \Exception
   */
  public function initWithId($entity_id = NULL, $cacheKey = NULL);

  /**
   * Factory with the Drupal entity.
   *
   * @param $entity
   * @return \CW\Controller\AbstractEntityController
   * @throws \EntityMalformedException
   */
  public function initWithEntity($entity);

  /**
   * Initialize a new controller with a creator factory.
   * Use creator instances to be able to produce the expected entity type/bundle.
   *
   * @param \CW\Factory\Creator $creator
   * @return \CW\Controller\AbstractEntityController
   */
  public function initNew(Creator $creator);

}
