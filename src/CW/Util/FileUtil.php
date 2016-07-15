<?php
/**
 * @file
 */

namespace CW\Util;

/**
 * Class FileUtil
 *
 * @package CW\Util
 *
 * File utilities.
 */
class FileUtil {

  // Mime types.
  const MIME_PNG = 'image/png';

  // Function param for self::uploadManagedFile().
  const NAME_USE_ORIGINAL = NULL;

  /**
   * @param string $uri Absolute location of the file in the filesystem.
   * @param string $mime
   * @param string $destFileName
   *  self::NAME_USE_ORIGINAL | string
   * @param string $destScheme
   * @return bool|\stdClass
   */
  public static function uploadManagedFile($uri, $mime = self::MIME_PNG, $destFileName = self::NAME_USE_ORIGINAL, $destScheme = 'public') {
    $source = (object) array('uri' => $uri, 'filemime' => $mime);
    $destFileName = $destFileName ?: basename($uri);
    return file_copy($source, $destScheme . '://' . $destFileName, FILE_EXISTS_RENAME);
  }

  /**
   * @param string $uri
   * @return null|string
   */
  public static function uriToRelativePath($uri) {
    $scheme = file_uri_scheme($uri);

    if (
      $scheme === 'public' &&
      ($wrapper = file_stream_wrapper_get_instance_by_scheme($scheme))
    ) {
      $path = $wrapper->getDirectoryPath() . '/' . file_uri_target($uri);
      return base_path() . $path;
    }

    return NULL;
  }

}
