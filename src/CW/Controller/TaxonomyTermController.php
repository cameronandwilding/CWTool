<?php
/**
 * @file
 * Simple taxonomy term controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

/**
 * Class TaxonomyTermController
 * @package CW\Controller
 *
 * Most basic implementation of taxonomy term controller.
 */
class TaxonomyTermController extends AbstractEntityController {

  // Entity type.
  const TYPE_TAXONOMY_TERM = 'taxonomy_term';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::TYPE_TAXONOMY_TERM;
  }

  /**
   * @return mixed
   */
  public function getName() {
    return $this->property('name');
  }

  /**
   * @return mixed
   */
  public function getDescription() {
    return $this->property('description');
  }
}

/**
 * @}
 */