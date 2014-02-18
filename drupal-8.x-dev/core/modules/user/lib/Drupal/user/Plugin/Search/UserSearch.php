<?php

/**
 * @file
 * Contains \Drupal\user\Plugin\Search\UserSearch.
 */

namespace Drupal\user\Plugin\Search;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\search\Plugin\SearchPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes a keyword search for users against the {users} database table.
 *
 * @SearchPlugin(
 *   id = "user_search",
 *   title = @Translation("Users")
 * )
 */
class UserSearch extends SearchPluginBase implements AccessibleInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  static public function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a UserSearch object.
   *
   * @param Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(Connection $database, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, array $configuration, $plugin_id, array $plugin_definition) {
    $this->database = $database;
    $this->entityManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL) {
    return !empty($account) && $account->hasPermission('access user profiles');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $results = array();
    if (!$this->isSearchExecutable()) {
      return $results;
    }
    $keys = $this->keywords;
    // Replace wildcards with MySQL/PostgreSQL wildcards.
    $keys = preg_replace('!\*+!', '%', $keys);
    $query = $this->database
      ->select('users')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('users', array('uid'));
    if ($this->currentUser->hasPermission('administer users')) {
      // Administrators can also search in the otherwise private email field, and
      // they don't need to be restricted to only active users.
      $query->fields('users', array('mail'));
      $query->condition($query->orConditionGroup()
        ->condition('name', '%' . $this->database->escapeLike($keys) . '%', 'LIKE')
        ->condition('mail', '%' . $this->database->escapeLike($keys) . '%', 'LIKE')
      );
    }
    else {
      // Regular users can only search via usernames, and we do not show them
      // blocked accounts.
      $query->condition('name', '%' . $this->database->escapeLike($keys) . '%', 'LIKE')
        ->condition('status', 1);
    }
    $uids = $query
      ->limit(15)
      ->execute()
      ->fetchCol();
    $accounts = $this->entityManager->getStorageController('user')->loadMultiple($uids);

    foreach ($accounts as $account) {
      $result = array(
        'title' => $account->getUsername(),
        'link' => url('user/' . $account->id(), array('absolute' => TRUE)),
      );
      if ($this->currentUser->hasPermission('administer users')) {
        $result['title'] .= ' (' . $account->getEmail() . ')';
      }
      $results[] = $result;
    }

    return $results;
  }

}
