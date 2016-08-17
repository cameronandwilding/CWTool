<?php
/**
 * @file
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

/**
 * Class ImageController
 *
 * @package CW\Controller
 *
 * Image controller.
 */
class ImageController extends FileController {

  /**
   * @var string
   */
  protected $alt;

  /**
   * @var string
   */
  protected $title;

  /**
   * @var int
   */
  protected $width;

  /**
   * @var int
   */
  protected $height;

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
      'alt' => $this->getAltFromHostField(),
      'title' => $this->getTitleFromHostField(),
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
      'alt' => $this->getAltFromHostField(),
      'title' => $this->getTitleFromHostField(),
    ]);
  }

  /**
   * Gets file mime type.
   *
   * @return string|null
   * @deprecated Use self::getAltFromHostField().
   */
  public function getAlt() {
    return $this->property('alt');
  }

  /**
   * Gets file mime type.
   *
   * @return string|null
   * @deprecated Use self::getTitleFromHostField().
   */
  public function getTitle() {
    return $this->property('title');
  }

  /**
   * Only available if controller was instantiated from an entity field.
   *
   * @return string
   */
  public function getAltFromHostField() {
    return @$this->alt;
  }

  /**
   * @param string $alt
   */
  protected function setAltFromHostField($alt) {
    $this->alt = $alt;
  }

  /**
   * Only available if controller was instantiated from an entity field.
   *
   * @return string
   */
  public function getTitleFromHostField() {
    return @$this->title;
  }

  /**
   * @param string $title
   */
  protected function setTitleFromHostField($title) {
    $this->title = $title;
  }

  /**
   * Only available if controller was instantiated from an entity field.
   *
   * @return int
   */
  public function getWidthFromHostField() {
    return @$this->width;
  }

  /**
   * @param int $width
   */
  protected function setWidthFromHostField($width) {
    $this->width = $width;
  }

  /**
   * Only available if controller was instantiated from an entity field.
   *
   * @return int
   */
  public function getHeightFromHostField() {
    return @$this->height;
  }

  /**
   * @param int $height
   */
  protected function setHeightFromHostField($height) {
    $this->height = $height;
  }

  protected function attachExtraReferencedControllerPropertiesFromParentController(array $fieldItem) {
    parent::attachExtraReferencedControllerPropertiesFromParentController($fieldItem);

    $this->setAltFromHostField(@$fieldItem['alt']);
    $this->setTitleFromHostField(@$fieldItem['title']);
    $this->setWidthFromHostField(@$fieldItem['width']);
    $this->setHeightFromHostField(@$fieldItem['height']);
  }

}
