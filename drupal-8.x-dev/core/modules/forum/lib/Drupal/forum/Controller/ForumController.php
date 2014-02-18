<?php

/**
 * @file
 * Contains \Drupal\forum\Controller\ForumController.
 */

namespace Drupal\forum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\forum\ForumManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageControllerInterface;
use Drupal\taxonomy\VocabularyStorageControllerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for forum routes.
 */
class ForumController extends ControllerBase {

  /**
   * Forum manager service.
   *
   * @var \Drupal\forum\ForumManagerInterface
   */
  protected $forumManager;

  /**
   * Vocabulary storage controller.
   *
   * @var \Drupal\taxonomy\VocabularyStorageControllerInterface
   */
  protected $vocabularyStorageController;

  /**
   * Term storage controller.
   *
   * @var \Drupal\taxonomy\TermStorageControllerInterface
   */
  protected $termStorageController;

  /**
   * Constructs a ForumController object.
   *
   * @param \Drupal\forum\ForumManagerInterface $forum_manager
   *   The forum manager service.
   * @param \Drupal\taxonomy\VocabularyStorageControllerInterface $vocabulary_storage_controller
   *   Vocabulary storage controller.
   * @param \Drupal\taxonomy\TermStorageControllerInterface $term_storage_controller
   *   Term storage controller.
   */
  public function __construct(ForumManagerInterface $forum_manager, VocabularyStorageControllerInterface $vocabulary_storage_controller, TermStorageControllerInterface $term_storage_controller) {
    $this->forumManager = $forum_manager;
    $this->vocabularyStorageController = $vocabulary_storage_controller;
    $this->termStorageController = $term_storage_controller;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('forum_manager'),
      $container->get('entity.manager')->getStorageController('taxonomy_vocabulary'),
      $container->get('entity.manager')->getStorageController('taxonomy_term')
    );
  }

  /**
   * Returns forum page for a given forum.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   The forum to render the page for.
   *
   * @return array
   *   A render array.
   */
  public function forumPage(TermInterface $taxonomy_term) {
    // Get forum details.
    $taxonomy_term->forums = $this->forumManager->getChildren($this->config('forum.settings')->get('vocabulary'), $taxonomy_term->id());
    $taxonomy_term->parents = $this->forumManager->getParents($taxonomy_term->id());

    if (empty($taxonomy_term->forum_container->value)) {
      $topics = $this->forumManager->getTopics($taxonomy_term->id());
    }
    else {
      $topics = '';
    }
    return $this->build($taxonomy_term->forums, $taxonomy_term, $topics, $taxonomy_term->parents);
  }

  /**
   * Returns forum index page.
   *
   * @return array
   *   A render array.
   */
  public function forumIndex() {
    $vocabulary = $this->vocabularyStorageController->load($this->config('forum.settings')->get('vocabulary'));
    $index = $this->forumManager->getIndex();
    $build = $this->build($index->forums, $index);
    if (empty($index->forums)) {
      // Root of empty forum.
      $build['#title'] = $this->t('No forums defined');
    }
    else {
      // Set the page title to forum's vocabulary name.
      $build['#title'] = $vocabulary->label();
    }
    return $build;
  }

  /**
   * Returns a renderable forum index page array.
   *
   * @param array $forums
   *   A list of forums.
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term of the forum.
   * @param array $topics
   *   The topics of this forum.
   * @param array $parents
   *   The parent forums in relation this forum.
   *
   * @return array
   *   A render array.
   */
  protected function build($forums, TermInterface $term, $topics = array(), $parents = array()) {
    $config = $this->config('forum.settings');
    $build = array(
      '#theme' => 'forums',
      '#forums' => $forums,
      '#topics' => $topics,
      '#parents' => $parents,
      '#term' => $term,
      '#sortby' => $config->get('topics.order'),
      '#forums_per_page' => $config->get('topics.page_limit'),
    );
    $build['#attached']['library'][] = array('forum', 'forum.index');
    if (empty($term->forum_container->value)) {
      $build['#attached']['drupal_add_feed'][] = array('taxonomy/term/' . $term->id() . '/feed', 'RSS - ' . $term->label());
    }

    return $build;
  }

  /**
   * Returns add forum entity form.
   *
   * @return array
   *   Render array for the add form.
   */
  public function addForum() {
    $vid = $this->config('forum.settings')->get('vocabulary');
    $taxonomy_term = $this->termStorageController->create(array(
      'vid' => $vid,
      'forum_controller' => 0,
    ));
    return $this->entityFormBuilder()->getForm($taxonomy_term, 'forum');
  }

  /**
   * Returns add container entity form.
   *
   * @return array
   *   Render array for the add form.
   */
  public function addContainer() {
    $vid = $this->config('forum.settings')->get('vocabulary');
    $taxonomy_term = $this->termStorageController->create(array(
      'vid' => $vid,
      'forum_container' => 1,
    ));
    return $this->entityFormBuilder()->getForm($taxonomy_term, 'container');
  }

}
