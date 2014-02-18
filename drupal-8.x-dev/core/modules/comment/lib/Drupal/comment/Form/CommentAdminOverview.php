<?php

/**
 * @file
 * Contains \Drupal\comment\Form\CommentAdminOverview.
 */

namespace Drupal\comment\Form;

use Drupal\comment\CommentInterface;
use Drupal\comment\CommentStorageControllerInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\Date;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the comments overview administration form.
 */
class CommentAdminOverview extends FormBase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The comment storage.
   *
   * @var \Drupal\comment\CommentStorageControllerInterface
   */
  protected $commentStorage;

  /**
   * The entity query service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Date service object.
   *
   * @var \Drupal\Core\Datetime\Date
   */
  protected $date;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates a CommentAdminOverview form.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager service.
   * @param \Drupal\comment\CommentStorageControllerInterface $comment_storage
   *   The comment storage.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query service.
   * @param \Drupal\Core\Datetime\Date $date
   *   The date service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityManager $entity_manager, CommentStorageControllerInterface $comment_storage, QueryFactory $entity_query, Date $date, ModuleHandlerInterface $module_handler) {
    $this->entityManager = $entity_manager;
    $this->commentStorage = $comment_storage;
    $this->entityQuery = $entity_query;
    $this->date = $date;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity.manager')->getStorageController('comment'),
      $container->get('entity.query'),
      $container->get('date'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'comment_admin_overview';
  }

  /**
   * Form constructor for the comment overview administration form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   * @param string $type
   *   The type of the overview form ('approval' or 'new').
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, array &$form_state, $type = 'new') {

    // Build an 'Update options' form.
    $form['options'] = array(
      '#type' => 'details',
      '#title' => $this->t('Update options'),
      '#attributes' => array('class' => array('container-inline')),
    );

    if ($type == 'approval') {
      $options['publish'] = $this->t('Publish the selected comments');
    }
    else {
      $options['unpublish'] = $this->t('Unpublish the selected comments');
    }
    $options['delete'] = $this->t('Delete the selected comments');

    $form['options']['operation'] = array(
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#title_display' => 'invisible',
      '#options' => $options,
      '#default_value' => 'publish',
    );
    $form['options']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    );

    // Load the comments that need to be displayed.
    $status = ($type == 'approval') ? CommentInterface::NOT_PUBLISHED : CommentInterface::PUBLISHED;
    $header = array(
      'subject' => array(
        'data' => $this->t('Subject'),
        'specifier' => 'subject',
      ),
      'author' => array(
        'data' => $this->t('Author'),
        'specifier' => 'name',
        'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      ),
      'posted_in' => array(
        'data' => $this->t('Posted in'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'changed' => array(
        'data' => $this->t('Updated'),
        'specifier' => 'changed',
        'sort' => 'desc',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'operations' => $this->t('Operations'),
    );
    $cids = $this->entityQuery->get('comment')
     ->condition('status', $status)
     ->tableSort($header)
     ->pager(50)
     ->execute();

    /** @var $comments \Drupal\comment\CommentInterface[] */
    $comments = $this->commentStorage->loadMultiple($cids);

    // Build a table listing the appropriate comments.
    $options = array();
    $destination = drupal_get_destination();

    $commented_entity_ids = array();
    $commented_entities = array();

    foreach ($comments as $comment) {
      $commented_entity_ids[$comment->getCommentedEntityTypeId()][] = $comment->getCommentedEntityId();
    }

    foreach ($commented_entity_ids as $entity_type => $ids) {
      $commented_entities[$entity_type] = $this->entityManager->getStorageController($entity_type)->loadMultiple($ids);
    }

    foreach ($comments as $comment) {
      /** @var $commented_entity \Drupal\Core\Entity\EntityInterface */
      $commented_entity = $commented_entities[$comment->getCommentedEntityTypeId()][$comment->getCommentedEntityId()];
      $commented_entity_uri = $commented_entity->urlInfo();
      $username = array(
        '#theme' => 'username',
        '#account' => comment_prepare_author($comment),
      );
      $body = '';
      if (!empty($comment->comment_body->value)) {
        $body = $comment->comment_body->value;
      }
      $comment_permalink = $comment->permalink();
      $options[$comment->id()] = array(
        'title' => array('data' => array('#title' => $comment->getSubject() ?: $comment->id())),
        'subject' => array(
          'data' => array(
            '#type' => 'link',
            '#title' => $comment->getSubject(),
            '#route_name' => $comment_permalink['route_name'],
            '#route_parameters' => $comment_permalink['route_parameters'],
            '#options' => $comment_permalink['options'] + array(
              'attributes' => array(
                'title' => Unicode::truncate($body, 128),
              ),
            ),
          ),
        ),
        'author' => drupal_render($username),
        'posted_in' => array(
          'data' => array(
            '#type' => 'link',
            '#title' => $commented_entity->label(),
            '#route_name' => $commented_entity_uri['route_name'],
            '#route_parameters' => $commented_entity_uri['route_parameters'],
            '#options' => $commented_entity_uri['options'],
            '#access' => $commented_entity->access('view'),
          ),
        ),
        'changed' => $this->date->format($comment->getChangedTime(), 'short'),
      );
      $comment_uri = $comment->urlInfo();
      $links = array();
      $links['edit'] = array(
        'title' => $this->t('edit'),
        'route_name' => 'comment.edit_page',
        'route_parameters' => array('comment' => $comment->id()),
        'options' => $comment_uri['options'],
        'query' => $destination,
      );
      if ($this->moduleHandler->invoke('content_translation', 'translate_access', array($comment))) {
        $links['translate'] = array(
          'title' => $this->t('translate'),
          'route_name' => 'content_translation.translation_overview_comment',
          'route_parameters' => array('comment' => $comment->id()),
          'options' => $comment_uri['options'],
          'query' => $destination,
        );
      }
      $options[$comment->id()]['operations']['data'] = array(
        '#type' => 'operations',
        '#links' => $links,
      );
    }

    $form['comments'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No comments available.'),
    );

    $form['pager'] = array('#theme' => 'pager');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    $form_state['values']['comments'] = array_diff($form_state['values']['comments'], array(0));
    // We can't execute any 'Update options' if no comments were selected.
    if (count($form_state['values']['comments']) == 0) {
      $this->setFormError('', $form_state, $this->t('Select one or more comments to perform the update on.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $operation = $form_state['values']['operation'];
    $cids = $form_state['values']['comments'];

    foreach ($cids as $cid) {
      // Delete operation handled in \Drupal\comment\Form\ConfirmDeleteMultiple
      // see \Drupal\comment\Controller\AdminController::adminPage().
      if ($operation == 'unpublish') {
        $comment = $this->commentStorage->load($cid);
        $comment->setPublished(FALSE);
        $comment->save();
      }
      elseif ($operation == 'publish') {
        $comment = $this->commentStorage->load($cid);
        $comment->setPublished(TRUE);
        $comment->save();
      }
    }
    drupal_set_message($this->t('The update has been performed.'));
    $form_state['redirect_route'] = array(
      'route_name' => 'comment.admin',
    );
    Cache::invalidateTags(array('content' => TRUE));
  }

}
