<?php
/**
 * @file
 * Simple taxonomy term controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;
use CW\Factory\EntityControllerFactory;

/**
 * Class TaxonomyTermController
 *
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

  /**
   * Get the relative path of the term. (Not transformed.)
   * Drupal's url can turn it to the transformed version.
   *
   * @return string
   */
  public function getPath() {
    return 'taxonomy/term/' . $this->getEntityId();
  }

  /**
   * Get the machine name of the vocabulary the term belongs to.
   *
   * @return string|null
   */
  public function getVocabularyMachineName() {
    return $this->property('vocabulary_machine_name');
  }

  /**
   * @param \CW\Factory\EntityControllerFactory $factory
   * @return TaxonomyTermController[]
   */
  public function getAllParentCtrl(EntityControllerFactory $factory) {
    return array_map(function ($term) use ($factory) {
      return $factory->initWithEntity($term);
    }, taxonomy_get_parents($this->getEntityId()));
  }

}

/**
 * @}
 */