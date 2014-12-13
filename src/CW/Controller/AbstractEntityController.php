<?php
/**
 * @file
 */

namespace CW\Controller;

use CW\Model\EntityModel;

/**
 * Class AbstractEntityController
 * @package CW\Controller
 *
 * Abstraction for entity controller. Contains the model and should be extended
 * for content specific behaviors.
 */
abstract class AbstractEntityController {

  /**
   * The model.
   *
   * @var \CW\Model\EntityModel
   */
  protected $entityModel;

  /**
   * Constructor.
   *
   * @param \CW\Model\EntityModel $entityModel
   *  The model object.
   */
  public function __construct(EntityModel $entityModel) {
    $this->entityModel = $entityModel;
  }

  /**
   * @return EntityModel
   */
  public function getEntityModel() {
    return $this->entityModel;
  }

  /**
   * @return \EntityDrupalWrapper
   */
  public function metadata() {
    return $this->getEntityModel()->getEntityMetadataWrapper();
  }

}
