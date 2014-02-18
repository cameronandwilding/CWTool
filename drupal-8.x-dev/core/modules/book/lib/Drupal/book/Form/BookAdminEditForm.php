<?php

/**
 * @file
 * Contains \Drupal\book\Form\BookAdminEditForm.
 */

namespace Drupal\book\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\menu_link\MenuLinkStorageControllerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for administering a single book's hierarchy.
 */
class BookAdminEditForm extends FormBase {

  /**
   * The menu cache object for this controller.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The node storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $nodeStorage;

  /**
   * The menu link storage controller.
   *
   * @var \Drupal\menu_link\MenuLinkStorageControllerInterface
   */
  protected $menuLinkStorage;

  /**
   * Constructs a new BookAdminEditForm.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The menu cache object to be used by this controller.
   * @param \Drupal\Core\Entity\EntityStorageControllerInterface $node_storage
   *   The custom block storage controller.
   * @param \Drupal\menu_link\MenuLinkStorageControllerInterface $menu_link_storage
   *   The custom block type storage controller.
   */
  public function __construct(CacheBackendInterface $cache, EntityStorageControllerInterface $node_storage, MenuLinkStorageControllerInterface $menu_link_storage) {
    $this->cache = $cache;
    $this->nodeStorage = $node_storage;
    $this->menuLinkStorage = $menu_link_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $container->get('cache.menu'),
      $entity_manager->getStorageController('node'),
      $entity_manager->getStorageController('menu_link')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_admin_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, NodeInterface $node = NULL) {
    $form['#title'] = $node->label();
    $form['#node'] = $node;
    $this->bookAdminTable($node, $form);
    $form['save'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save book pages'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    if ($form_state['values']['tree_hash'] != $form_state['values']['tree_current_hash']) {
      $this->setFormError('', $form_state, $this->t('This book has been modified by another user, the changes could not be saved.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    // Save elements in the same order as defined in post rather than the form.
    // This ensures parents are updated before their children, preventing orphans.
    $order = array_flip(array_keys($form_state['input']['table']));
    $form['table'] = array_merge($order, $form['table']);

    // Track updates.
    $updated = FALSE;
    foreach (element_children($form['table']) as $key) {
      if ($form['table'][$key]['#item']) {
        $row = $form['table'][$key];
        $values = $form_state['values']['table'][$key];

        // Update menu item if moved.
        if ($row['plid']['#default_value'] != $values['plid'] || $row['weight']['#default_value'] != $values['weight']) {
          $menu_link = $this->menuLinkStorage->load($values['mlid']);
          $menu_link->weight = $values['weight'];
          $menu_link->plid = $values['plid'];
          $menu_link->save();
          $updated = TRUE;
        }

        // Update the title if changed.
        if ($row['title']['#default_value'] != $values['title']) {
          $node = $this->nodeStorage->load($values['nid']);
          $node->log = $this->t('Title changed from %original to %current.', array('%original' => $node->label(), '%current' => $values['title']));
          $node->title = $values['title'];
          $node->book['link_title'] = $values['title'];
          $node->setNewRevision();
          $node->save();
          watchdog('content', 'book: updated %title.', array('%title' => $node->label()), WATCHDOG_NOTICE, l($this->t('view'), 'node/' . $node->id()));
        }
      }
    }
    if ($updated) {
      // Flush static and cache.
      drupal_static_reset('book_menu_subtree_data');
      $cid = 'links:' . $form['#node']->book['menu_name'] . ':subtree-cid:' . $form['#node']->book['mlid'];
      $this->cache->delete($cid);
    }

    drupal_set_message($this->t('Updated book %title.', array('%title' => $form['#node']->label())));
  }

  /**
   * Builds the table portion of the form for the book administration page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node of the top-level page in the book.
   * @param array $form
   *   The form that is being modified, passed by reference.
   *
   * @see self::buildForm()
   */
  protected function bookAdminTable(NodeInterface $node, array &$form) {
    $form['table'] = array(
      '#theme' => 'book_admin_table',
      '#tree' => TRUE,
    );

    $tree = \Drupal::service('book.manager')->bookMenuSubtreeData($node->book);
    // Do not include the book item itself.
    $tree = array_shift($tree);
    if ($tree['below']) {
      $hash = Crypt::hashBase64(serialize($tree['below']));
      // Store the hash value as a hidden form element so that we can detect
      // if another user changed the book hierarchy.
      $form['tree_hash'] = array(
        '#type' => 'hidden',
        '#default_value' => $hash,
      );
      $form['tree_current_hash'] = array(
        '#type' => 'value',
        '#value' => $hash,
      );
      $this->bookAdminTableTree($tree['below'], $form['table']);
    }
  }

  /**
   * Helps build the main table in the book administration page form.
   *
   * @param array $tree
   *   A subtree of the book menu hierarchy.
   * @param array $form
   *   The form that is being modified, passed by reference.
   *
   * @see self::buildForm()
   */
  protected function bookAdminTableTree(array $tree, array &$form) {
    // The delta must be big enough to give each node a distinct value.
    $count = count($tree);
    $delta = ($count < 30) ? 15 : intval($count / 2) + 1;

    foreach ($tree as $data) {
      $form['book-admin-' . $data['link']['nid']] = array(
        '#item' => $data['link'],
        'nid' => array('#type' => 'value', '#value' => $data['link']['nid']),
        'depth' => array('#type' => 'value', '#value' => $data['link']['depth']),
        'href' => array('#type' => 'value', '#value' => $data['link']['href']),
        'title' => array(
          '#type' => 'textfield',
          '#default_value' => $data['link']['link_title'],
          '#maxlength' => 255,
          '#size' => 40,
        ),
        'weight' => array(
          '#type' => 'weight',
          '#default_value' => $data['link']['weight'],
          '#delta' => max($delta, abs($data['link']['weight'])),
          '#title' => $this->t('Weight for @title', array('@title' => $data['link']['title'])),
          '#title_display' => 'invisible',
        ),
        'plid' => array(
          '#type' => 'hidden',
          '#default_value' => $data['link']['plid'],
        ),
        'mlid' => array(
          '#type' => 'hidden',
          '#default_value' => $data['link']['mlid'],
        ),
      );
      if ($data['below']) {
        $this->bookAdminTableTree($data['below'], $form);
      }
    }
  }

}
