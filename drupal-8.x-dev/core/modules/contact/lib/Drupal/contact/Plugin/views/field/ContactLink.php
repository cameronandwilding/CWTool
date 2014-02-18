<?php

/**
 * @file
 * Contains \Drupal\contact\Plugin\views\field\ContactLink.
 */

namespace Drupal\contact\Plugin\views\field;

use Drupal\Core\Access\AccessManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Plugin\views\field\Link;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a field that links to the user contact page, if access is permitted.
 *
 * @ingroup views_field_handlers
 *
 * @PluginID("contact_link")
 */
class ContactLink extends Link {

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManager
   */
  protected $accessManager;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Gets the current active user.
   *
   * @todo: https://drupal.org/node/2105123 put this method in
   *   \Drupal\Core\Plugin\PluginBase instead.
   *
   * @return \Drupal\Core\Session\AccountInterface
   */
  protected function currentUser() {
    if (!$this->currentUser) {
      $this->currentUser = \Drupal::currentUser();
    }
    return $this->currentUser;
  }

  /**
   * Constructs a ContactLink object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Access\AccessManager $access_manager
   *   The access manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccessManager $access_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->accessManager = $access_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('access_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, &$form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['text']['#title'] = t('Link label');
    $form['text']['#required'] = TRUE;
    $form['text']['#default_value'] = empty($this->options['text']) ? t('contact') : $this->options['text'];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // The access logic is implemented per row.
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  protected function renderLink(EntityInterface $entity, ResultRow $values) {

    if (empty($entity)) {
      return;
    }

    // Check access when we pull up the user account so we know
    // if the user has made the contact page available.
    if (!$this->accessManager->checkNamedRoute('contact.personal_page', array('user' => $entity->id()), $this->currentUser())) {
      return;
    }

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "user/{$entity->id()}/contact";

    $title = t('Contact %user', array('%user' => $entity->name->value));
    $this->options['alter']['attributes'] = array('title' => $title);

    if (!empty($this->options['text'])) {
      return $this->options['text'];
    }
    else {
      return $title;
    }
  }

}
