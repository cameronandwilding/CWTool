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

//  protected static $factory;
//  public static function factory() {
//    if (empty(self::$factory)) {
//      self::$factory = new EntityControllerFactory();
//    }
//  }

}
