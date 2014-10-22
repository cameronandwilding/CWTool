<?php
/**
 * @file
 *
 * Issue content model.
 */

/**
 * Class ExampleContentTypeWrapper
 */
class ExampleContentTypeWrapper extends CWToolNodeWrapper {

  const NAME = 'Blog';

  public function getCoverImage() {
    // Self node related operations.
    return $this->field_blog_cover_image->uri->value();
  }

}
