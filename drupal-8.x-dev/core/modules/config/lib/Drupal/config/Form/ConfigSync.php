<?php

/**
 * @file
 * Contains \Drupal\config\Form\ConfigSync.
 */

namespace Drupal\config\Form;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigException;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Construct the storage changes in a configuration synchronization form.
 */
class ConfigSync extends FormBase {

  /**
   * The database lock object.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The source configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $sourceStorage;

  /**
   * The target configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $targetStorage;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface;
   */
  protected $configManager;

  /**
   * URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManager
   */
  protected $typedConfigManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Config\StorageInterface $sourceStorage
   *   The source storage object.
   * @param \Drupal\Core\Config\StorageInterface $targetStorage
   *   The target storage manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Config\ConfigManager
   *   Configuration manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   * @param \Drupal\Core\Config\TypedConfigManager $typed_config
   *   The typed configuration manager.
   */
  public function __construct(StorageInterface $sourceStorage, StorageInterface $targetStorage, LockBackendInterface $lock, EventDispatcherInterface $event_dispatcher, ConfigManagerInterface $config_manager, UrlGeneratorInterface $url_generator, TypedConfigManager $typed_config) {
    $this->sourceStorage = $sourceStorage;
    $this->targetStorage = $targetStorage;
    $this->lock = $lock;
    $this->eventDispatcher = $event_dispatcher;
    $this->configManager = $config_manager;
    $this->urlGenerator = $url_generator;
    $this->typedConfigManager = $typed_config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.storage.staging'),
      $container->get('config.storage'),
      $container->get('lock'),
      $container->get('event_dispatcher'),
      $container->get('config.manager'),
      $container->get('url_generator'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_admin_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Import all'),
    );

    $source_list = $this->sourceStorage->listAll();
    $storage_comparer = new StorageComparer($this->sourceStorage, $this->targetStorage);
    if (empty($source_list) || !$storage_comparer->createChangelist()->hasChanges()) {
      $form['no_changes'] = array(
        '#theme' => 'table',
        '#header' => array('Name', 'Operations'),
        '#rows' => array(),
        '#empty' => $this->t('There are no configuration changes.'),
      );
      $form['actions']['#access'] = FALSE;
      return $form;
    }
    elseif (!$storage_comparer->validateSiteUuid()) {
      drupal_set_message($this->t('The staged configuration cannot be imported, because it originates from a different site than this site. You can only synchronize configuration between cloned instances of this site.'), 'error');
      $form['actions']['#access'] = FALSE;
      return $form;
    }
    else {
      // Store the comparer for use in the submit.
      $form_state['storage_comparer'] = $storage_comparer;
    }

    // Add the AJAX library to the form for dialog support.
    $form['#attached']['library'][] = array('system', 'drupal.ajax');

    foreach ($storage_comparer->getChangelist() as $config_change_type => $config_files) {
      if (empty($config_files)) {
        continue;
      }

      // @todo A table caption would be more appropriate, but does not have the
      //   visual importance of a heading.
      $form[$config_change_type]['heading'] = array(
        '#type' => 'html_tag',
        '#tag' => 'h3',
      );
      switch ($config_change_type) {
        case 'create':
          $form[$config_change_type]['heading']['#value'] = format_plural(count($config_files), '@count new', '@count new');
          break;

        case 'update':
          $form[$config_change_type]['heading']['#value'] = format_plural(count($config_files), '@count changed', '@count changed');
          break;

        case 'delete':
          $form[$config_change_type]['heading']['#value'] = format_plural(count($config_files), '@count removed', '@count removed');
          break;
      }
      $form[$config_change_type]['list'] = array(
        '#theme' => 'table',
        '#header' => array('Name', 'Operations'),
      );

      foreach ($config_files as $config_file) {
        $links['view_diff'] = array(
          'title' => $this->t('View differences'),
          'href' => $this->urlGenerator->getPathFromRoute('config.diff', array('config_file' => $config_file)),
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
            'data-dialog-options' => json_encode(array(
              'width' => 700
            )),
          ),
        );
        $form[$config_change_type]['list']['#rows'][] = array(
          'name' => $config_file,
          'operations' => array(
            'data' => array(
              '#type' => 'operations',
              '#links' => $links,
            ),
          ),
        );
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config_importer = new ConfigImporter(
      $form_state['storage_comparer'],
      $this->eventDispatcher,
      $this->configManager,
      $this->lock,
      $this->typedConfigManager
    );
    if ($config_importer->alreadyImporting()) {
      drupal_set_message($this->t('Another request may be synchronizing configuration already.'));
    }
    else{
      try {
        $config_importer->import();
        drupal_flush_all_caches();
        drupal_set_message($this->t('The configuration was imported successfully.'));
      }
      catch (ConfigException $e) {
        // Return a negative result for UI purposes. We do not differentiate
        // between an actual synchronization error and a failed lock, because
        // concurrent synchronizations are an edge-case happening only when
        // multiple developers or site builders attempt to do it without
        // coordinating.
        watchdog_exception('config_import', $e);
        drupal_set_message($this->t('The import failed due to an error. Any errors have been logged.'), 'error');
      }
    }
  }

}
