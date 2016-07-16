<?php
/**
 * @file
 */

namespace CW\Controller;

/**
 * Class FieldCollectionController
 *
 * @package CW\Controller
 *
 * Field collection controller base.
 */
class FieldCollectionController extends AbstractEntityController {

  const ENTITY_TYPE = 'field_collection_item';

  public static function getClassEntityType() {
    return self::ENTITY_TYPE;
  }

}
