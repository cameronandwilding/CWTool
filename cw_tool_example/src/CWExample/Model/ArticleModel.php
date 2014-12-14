<?php
/**
 * @file
 */

namespace CWExample\Model;

use CW\Model\EntityModel;

class ArticleModel extends EntityModel {

  public function createdDate() {
    return gmdate('Y-m-d', $this->getEntityData()->created);
  }

}
