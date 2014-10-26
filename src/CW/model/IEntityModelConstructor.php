<?php
/**
 * Created by PhpStorm.
 * User: itarato
 * Date: 25/10/14
 * Time: 00:57
 */

namespace CW\Model;

interface IEntityModelConstructor {

  public function __construct($entity_type, $entity_id);

}