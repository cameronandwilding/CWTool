<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\FileController;
use CW\Test\TestCase;

class FileControllerTest extends TestCase {

  public function testClassProperties() {
    $this->assertEquals(FileController::getClassEntityType(), FileController::ENTITY_TYPE);
  }

}
