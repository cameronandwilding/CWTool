<?php

namespace Drupal\devel_generate;

define('DEVEL_GENERATE_IMAGE_MAX', 5);

class DevelGenerateFieldImage extends DevelGenerateFieldBase {

  function generateValues($object, $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    static $images = array();
      $settings = $instance->getSettings();

    $min_resolution = empty($settings['min_resolution']) ? '100x100' : $settings['min_resolution'];
    $max_resolution = empty($settings['max_resolution']) ? '600x600' : $settings['max_resolution'];
    $extensions = array_intersect(explode(' ', $settings['file_extensions']), array('png', 'gif', 'jpg', 'jpeg'));
    $extension = array_rand(array_combine($extensions, $extensions));
    // Generate a max of 5 different images.
    if (!isset($images[$extension][$min_resolution][$max_resolution]) || count($images[$extension][$min_resolution][$max_resolution]) <= DEVEL_GENERATE_IMAGE_MAX) {
      if ($path = $this->generateImage($extension, $min_resolution, $max_resolution)) {
        $account = user_load(1);
        $image = entity_create('file', array());
        $image->setFileUri($path);
        $image->setOwner($account);
        $image->setMimeType('image/' . pathinfo($path, PATHINFO_EXTENSION));
        $image->setFileName(drupal_basename($path));
        $destination_dir = $settings['uri_scheme'] . '://' . $settings['file_directory'];
        file_prepare_directory($destination_dir, FILE_CREATE_DIRECTORY);
        $destination = $destination_dir . '/' . basename($path);
        $file = file_move($image, $destination, FILE_CREATE_DIRECTORY);
        $images[$extension][$min_resolution][$max_resolution][$file->id()] = $file;
      }
      else {
        return FALSE;
      }
    }
    else {
      // Select one of the images we've already generated for this field.
      $image_index = array_rand($images[$extension][$min_resolution][$max_resolution]);
      $file = $images[$extension][$min_resolution][$max_resolution][$image_index];
    }

    $object_field['target_id'] = $file->id();
    $object_field['alt'] = DevelGenerateBase::createGreeking(4);
    $object_field['title'] = DevelGenerateBase::createGreeking(4);
    return $object_field;
  }

  /**
   * Private function for creating a random image.
   *
   * This function only works with the GD toolkit. ImageMagick is not supported.
   */
  protected function generateImage($extension = 'png', $min_resolution, $max_resolution) {
    if ($tmp_file = drupal_tempnam('temporary://', 'imagefield_')) {
      $destination = $tmp_file . '.' . $extension;
      file_unmanaged_move($tmp_file, $destination, FILE_CREATE_DIRECTORY);

      $min = explode('x', $min_resolution);
      $max = explode('x', $max_resolution);

      $width = rand((int)$min[0], (int)$max[0]);
      $height = rand((int)$min[1], (int)$max[1]);

      // Make an image split into 4 sections with random colors.
      $im = imagecreate($width, $height);
      for ($n = 0; $n < 4; $n++) {
        $color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
        $x = $width/2 * ($n % 2);
        $y = $height/2 * (int) ($n >= 2);
        imagefilledrectangle($im, $x, $y, $x + $width/2, $y + $height/2, $color);
      }

      // Make a perfect circle in the image middle.
      $color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
      $smaller_dimension = min($width, $height);
      $smaller_dimension = ($smaller_dimension % 2) ? $smaller_dimension : $smaller_dimension;
      imageellipse($im, $width/2, $height/2, $smaller_dimension, $smaller_dimension, $color);

      $save_function = 'image'. ($extension == 'jpg' ? 'jpeg' : $extension);
      $save_function($im, drupal_realpath($destination));
      return $destination;
    }

  }

}
