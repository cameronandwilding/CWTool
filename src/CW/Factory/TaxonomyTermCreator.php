<?php
/**
 * @file
 */

namespace CW\Factory;

use CW\Params\TaxonomyTermCreationParam;

/**
 * Class TaxonomyTermCreator
 *
 * @package CW\Factory
 *
 * Taxonomy term entity creator.
 */
class TaxonomyTermCreator implements Creator {

  /**
   * @var \CW\Params\TaxonomyTermCreationParam
   */
  private $taxonomyTermCreationParam;

  /**
   * TaxonomyTermCreator constructor.
   *
   * @param \CW\Params\TaxonomyTermCreationParam $taxonomyTermCreationParam
   */
  public function __construct(TaxonomyTermCreationParam $taxonomyTermCreationParam) {
    $this->taxonomyTermCreationParam = $taxonomyTermCreationParam;
  }

  /**
   * {@inheritdoc}
   */
  public function create() {
    $term = (object) $this->taxonomyTermCreationParam->getExtraAttributes();
    $result = taxonomy_term_save($term);
    return ($result === SAVED_NEW || $result === SAVED_UPDATED) ? $term : NULL;
  }

}
