<?php
/**
 * @file
 */

namespace CWExample\Controller;

use CW\Controller\NodeController;

class ArticleController extends NodeController {

  public function getTitle() {
    return $this->data()->title;
  }

  public function updateTitle() {
    $this->data()->title = 'Example title - ' . microtime(TRUE);
    $this->entityModel->setDirty();
  }

}
