<?php

/**
 * @file
 * Contains \Drupal\book\Form\BookOutlineForm.
 */

namespace Drupal\book\Form;

use Drupal\Core\Entity\ContentEntityFormController;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\book\BookManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays the book outline form.
 */
class BookOutlineForm extends ContentEntityFormController {

  /**
   * The book being displayed.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $entity;

  /**
   * BookManager service.
   *
   * @var \Drupal\book\BookManager
   */
  protected $bookManager;

  /**
   * Constructs a BookOutlineForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\book\BookManager $book_manager
   *   The BookManager service.
   */
  public function __construct(EntityManagerInterface $entity_manager, BookManager $book_manager) {
    parent::__construct($entity_manager);
    $this->bookManager = $book_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('book.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormID() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form['#title'] = $this->entity->label();

    if (!isset($this->entity->book)) {
      // The node is not part of any book yet - set default options.
      $this->entity->book = $this->bookManager->getLinkDefaults($this->entity->id());
    }
    else {
      $this->entity->book['original_bid'] = $this->entity->book['bid'];
    }

    // Find the depth limit for the parent select.
    if (!isset($this->entity->book['parent_depth_limit'])) {
      $this->entity->book['parent_depth_limit'] = $this->bookManager->getParentDepthLimit($this->entity->book);
    }
    $form = $this->bookManager->addFormElements($form, $form_state, $this->entity, $this->currentUser());

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->entity->book['original_bid'] ? $this->t('Update book outline') : $this->t('Add to book outline');
    $actions['delete']['#value'] = $this->t('Remove from book outline');
    $actions['delete']['#access'] = $this->bookManager->checkNodeIsRemovable($this->entity);
    return $actions;
  }

  /**
   * {@inheritdoc}
   *
   * @see book_remove_button_submit()
   */
  public function submit(array $form, array &$form_state) {
    $form_state['redirect_route'] = array(
      'route_name' => 'node.view',
      'route_parameters' => array(
        'node' => $this->entity->id(),
      ),
    );
    $book_link = $form_state['values']['book'];
    if (!$book_link['bid']) {
      drupal_set_message($this->t('No changes were made'));
      return;
    }

    $book_link['menu_name'] = $this->bookManager->createMenuName($book_link['bid']);
    $this->entity->book = $book_link;
    if ($this->bookManager->updateOutline($this->entity)) {
      if ($this->entity->book['parent_mismatch']) {
        // This will usually only happen when JS is disabled.
        drupal_set_message($this->t('The post has been added to the selected book. You may now position it relative to other pages.'));
        $form_state['redirect_route'] = $this->entity->urlInfo('book-outline-form');
      }
      else {
        drupal_set_message($this->t('The book outline has been updated.'));
      }
    }
    else {
      drupal_set_message($this->t('There was an error adding the post to the book.'), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $form_state['redirect_route'] = $this->entity->urlInfo('book-remove-form');
  }

}
