<?php
/**
 * @file
 */

namespace CW\Params;

/**
 * Class TaxonomyTermCreationParam
 *
 * @package CW\Params
 *
 * Creation param type for taxonomy terms.
 */
class TaxonomyTermCreationParam extends EntityCreationParams {

  /**
   * TaxonomyTermCreationParam constructor.
   *
   * @param int $vid
   * @param string $name
   * @param array $extraAttributes
   */
  public function __construct($vid, $name, array $extraAttributes = []) {
    parent::__construct($extraAttributes);

    $this->setProperty('vid', $vid);
    $this->setProperty('name', $name);
  }

  /**
   * @param string $description
   */
  public function setDescription($description) {
    $this->setProperty('description', $description);
  }

  /**
   * @param string $format
   */
  public function setFormat($format) {
    $this->setProperty('format', $format);
  }

  /**
   * @param int $weight
   */
  public function setWeight($weight) {
    $this->setProperty('weight', $weight);
  }

  /**
   * @param int $parent
   */
  public function setParent($parent) {
    $this->setProperty('parent', $parent);
  }

}
