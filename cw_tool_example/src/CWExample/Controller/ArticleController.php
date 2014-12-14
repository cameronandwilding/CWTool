<?php
/**
 * @file
 */

namespace CWExample\Controller;

use CW\Controller\BasicEntityController;

class ArticleController extends BasicEntityController {

  public function getTitle() {
    return $this->data()->title;
  }

}
