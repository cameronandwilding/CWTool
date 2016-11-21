<?php
/**
 * @file
 * Simple taxonomy term controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;
use CW\Factory\EntityControllerFactoryInterface;

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
   * @param \CW\Factory\EntityControllerFactoryInterface $factory
   * @return TaxonomyTermController[]
   */
  public function getAllParentCtrl(EntityControllerFactoryInterface $factory) {
    return array_map(function ($term) use ($factory) {
      return $factory->initWithEntity($term);
    }, taxonomy_get_parents($this->getEntityId()));
  }

  /**
   * @param \CW\Factory\EntityControllerFactoryInterface $factory
   * @return []
   */
  public function getAllParentNames(EntityControllerFactoryInterface $factory) {
    return array_map(function ($term) {
      /** @var TaxonomyTermController $term */
      return $term->getName();
    }, $this->getAllParentCtrl($factory));
  }

  /**
   * Get weight or PHP_INT_MAX if does not exist.
   *
   * @return int
   */
  public function getWeight() {
    $entity = $this->entity();
    return isset($entity->weight) && is_numeric($entity->weight) ? (int) $entity->weight : PHP_INT_MAX;
  }

  /**
   * @return object
   */
  public static function getVocabulary() {
    return taxonomy_vocabulary_machine_name_load(static::getClassEntityBundle());
  }

}

/**
 * @}
 */