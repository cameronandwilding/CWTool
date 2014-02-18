<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityFormBuilderInterface.
 */

namespace Drupal\Core\Entity;

/**
 * Builds entity forms.
 */
interface EntityFormBuilderInterface {

  /**
   * Returns the built and processed entity form for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be created or edited.
   * @param string $operation
   *   (optional) The operation identifying the form variation to be returned.
   *   Defaults to 'default'.
   * @param array $form_state
   *   (optional) An associative array containing the current state of the form.
   *   Use this to pass additional information to the form, such as the
   *   langcode. Defaults to an empty array.
   *
   * @code
   *   $form_state['langcode'] = $langcode;
   *   $form = \Drupal::service('entity.form_builder')->getForm($entity, 'default', $form_state);
   * @endcode
   *
   * @return array
   *   The processed form for the given entity and operation.
   */
  public function getForm(EntityInterface $entity, $operation = 'default', array $form_state = array());

}
