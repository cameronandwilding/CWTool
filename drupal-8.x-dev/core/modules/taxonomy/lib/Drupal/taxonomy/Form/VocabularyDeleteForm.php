<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Form\VocabularyDeleteForm.
 */

namespace Drupal\taxonomy\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a deletion confirmation form for taxonomy vocabulary.
 */
class VocabularyDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_vocabulary_confirm_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the vocabulary %title?', array('%title' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'taxonomy.vocabulary_list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a vocabulary will delete all the terms in it. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    drupal_set_message($this->t('Deleted vocabulary %name.', array('%name' => $this->entity->label())));
    watchdog('taxonomy', 'Deleted vocabulary %name.', array('%name' => $this->entity->label()), WATCHDOG_NOTICE);
    $form_state['redirect_route']['route_name'] = 'taxonomy.vocabulary_list';
    Cache::invalidateTags(array('content' => TRUE));
  }

}
