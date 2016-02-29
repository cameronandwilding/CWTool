<?php
/**
 * @file
 */

namespace CW\Util;

use CW\Adapter\UtilityCollectionInterface;
use CW\Factory\SelfFactory;

/**
 * Class BasicUtilityCollection
 *
 * @package CW\Util
 *
 * Basic utility functions.
 * For explanation to utility collections:
 * @see CW\Adapter\UtilityCollectionInterface
 */
class BasicUtilityCollection implements UtilityCollectionInterface {

  use SelfFactory;

  /**
   * @param int $length
   * @return string
   */
  public function randomString($length = 8) {
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;
    $str = '';
    for ($i = 0; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $str;
  }

  /**
   * @param int $length
   * @return string
   */
  public function randomWords($length = 128) {
    $out = '';
    while ($length > 0) {
      $word_length = min(rand(4, 10), $length);
      $length -= $word_length;
      $out .= static::randomString($word_length);
      $out .= $length > 0 ? ' ' : '';
    }
    return $out;
  }

  /**
   * @param int $max
   * @param int $min
   * @return int
   */
  public function randomInt($max = PHP_INT_MAX, $min = 0) {
    return rand($min, $max);
  }

  /**
   * @param string $vocabularyName
   * @param string $termName
   * @return NULL|int
   */
  public function findTaxonomyTermID($vocabularyName, $termName) {
    if (!($terms = taxonomy_get_term_by_name($termName, $vocabularyName))) {
      return NULL;
    }
    $tids = array_keys($terms);
    return reset($tids);
  }

  /**
   * @return NULL|int
   */
  public function uploadFileAndGetFID() {
    $source = (object) array(
      'uri' => __DIR__ . '/../../../assets/sample.png',
    );
    $file = file_copy($source, 'public://cw_tool_creator_sample.png', FILE_EXISTS_RENAME);
    return @$file->fid;
  }

}
