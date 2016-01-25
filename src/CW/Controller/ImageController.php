<?php
/**
 * @file
 *
 * Image controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

/**
 * Class ImageController
 * @package CW\Controller
 */
class ImageController extends FileController {

  /**
   * Renders an image.
   *
   * @return string
   *   HTML of the image rendered.
   *
   * @throws \Exception
   */
  public function render() {
    return theme('image', [
      'path' => $this->getFileURI(),
      'alt' => $this->getAlt(),
      'title' => $this->getTitle(),
    ]);
  }

  /**
   * Renders an image applying a style.
   *
   * @param string $style
   *   Style to apply.
   *
   * @return string
   *   HTML of the image rendered.
   *
   * @throws \Exception
   */
  public function renderStyle($style) {
    return theme('image_style', [
      'style_name' => $style,
      'path' => $this->getFileURI(),
      'alt' => $this->getAlt(),
      'title' => $this->getTitle(),
    ]);
  }

  /**
   * Gets file mime type.
   *
   * @return string|null
   */
  public function getAlt() {
    return $this->property('alt');
  }

  /**
   * Gets file mime type.
   *
   * @return string|null
   */
  public function getTitle() {
    return $this->property('title');
  }
}