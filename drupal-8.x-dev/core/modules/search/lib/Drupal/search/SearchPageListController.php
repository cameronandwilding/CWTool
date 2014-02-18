<?php

/**
 * @file
 * Contains \Drupal\search\SearchPageListController.
 */

namespace Drupal\search;

use Drupal\Component\Utility\MapArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\DraggableListController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of search page entities.
 */
class SearchPageListController extends DraggableListController implements FormInterface {

  /**
   * The entities being listed.
   *
   * @var \Drupal\search\SearchPageInterface[]
   */
  protected $entities = array();

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The search manager.
   *
   * @var \Drupal\search\SearchPluginManager
   */
  protected $searchManager;

  /**
   * Constructs a new SearchPageListController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageControllerInterface $storage
   *   The entity storage controller class.
   * @param \Drupal\search\SearchPluginManager $search_manager
   *   The search plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageControllerInterface $storage, SearchPluginManager $search_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($entity_type, $storage);
    $this->configFactory = $config_factory;
    $this->searchManager = $search_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorageController($entity_type->id()),
      $container->get('plugin.manager.search'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'search_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = array(
      'data' => $this->t('Label'),
    );
    $header['url'] = array(
      'data' => $this->t('URL'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );
    $header['plugin'] = array(
      'data' => $this->t('Type'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );
    $header['status'] = array(
      'data' => $this->t('Status'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var $entity \Drupal\search\SearchPageInterface */
    $row['label'] = $this->getLabel($entity);
    $row['url']['#markup'] = 'search/' . $entity->getPath();
    // If the search page is active, link to it.
    if ($entity->status()) {
      $row['url'] = array(
        '#type' => 'link',
        '#title' => $row['url'],
        '#route_name' => 'search.view_' . $entity->id(),
      );
    }

    $definition = $entity->getPlugin()->getPluginDefinition();
    $row['plugin']['#markup'] = $definition['title'];

    if ($entity->isDefaultSearch()) {
      $status = $this->t('Default');
    }
    elseif ($entity->status()) {
      $status = $this->t('Enabled');
    }
    else {
      $status = $this->t('Disabled');
    }
    $row['status']['#markup'] = $status;
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form = parent::buildForm($form, $form_state);
    $old_state = $this->configFactory->getOverrideState();
    $search_settings = $this->configFactory->setOverrideState(FALSE)->get('search.settings');
    $this->configFactory->setOverrideState($old_state);
    // Collect some stats.
    $remaining = 0;
    $total = 0;
    foreach ($this->entities as $entity) {
      if ($entity->isIndexable() && $status = $entity->getPlugin()->indexStatus()) {
        $remaining += $status['remaining'];
        $total += $status['total'];
      }
    }

    $this->moduleHandler->loadAllIncludes('admin.inc');
    $count = format_plural($remaining, 'There is 1 item left to index.', 'There are @count items left to index.');
    $percentage = ((int) min(100, 100 * ($total - $remaining) / max(1, $total))) . '%';
    $status = '<p><strong>' . $this->t('%percentage of the site has been indexed.', array('%percentage' => $percentage)) . ' ' . $count . '</strong></p>';
    $form['status'] = array(
      '#type' => 'details',
      '#title' => $this->t('Indexing status'),
    );
    $form['status']['status'] = array('#markup' => $status);
    $form['status']['wipe'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Re-index site'),
      '#submit' => array(array($this, 'searchAdminReindexSubmit')),
    );

    $items = MapArray::copyValuesToKeys(array(10, 20, 50, 100, 200, 500));

    // Indexing throttle:
    $form['indexing_throttle'] = array(
      '#type' => 'details',
      '#title' => $this->t('Indexing throttle')
    );
    $form['indexing_throttle']['cron_limit'] = array(
      '#type' => 'select',
      '#title' => $this->t('Number of items to index per cron run'),
      '#default_value' => $search_settings->get('index.cron_limit'),
      '#options' => $items,
      '#description' => $this->t('The maximum number of items indexed in each pass of a <a href="@cron">cron maintenance task</a>. If necessary, reduce the number of items to prevent timeouts and memory errors while indexing.', array('@cron' => url('admin/reports/status'))),
    );
    // Indexing settings:
    $form['indexing_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Indexing settings')
    );
    $form['indexing_settings']['info'] = array(
      '#markup' => $this->t('<p><em>Changing the settings below will cause the site index to be rebuilt. The search index is not cleared but systematically updated to reflect the new settings. Searching will continue to work but new content won\'t be indexed until all existing content has been re-indexed.</em></p><p><em>The default settings should be appropriate for the majority of sites.</em></p>')
    );
    $form['indexing_settings']['minimum_word_size'] = array(
      '#type' => 'number',
      '#title' => $this->t('Minimum word length to index'),
      '#default_value' => $search_settings->get('index.minimum_word_size'),
      '#min' => 1,
      '#max' => 1000,
      '#description' => $this->t('The number of characters a word has to be to be indexed. A lower setting means better search result ranking, but also a larger database. Each search query must contain at least one keyword that is this size (or longer).')
    );
    $form['indexing_settings']['overlap_cjk'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Simple CJK handling'),
      '#default_value' => $search_settings->get('index.overlap_cjk'),
      '#description' => $this->t('Whether to apply a simple Chinese/Japanese/Korean tokenizer based on overlapping sequences. Turn this off if you want to use an external preprocessor for this instead. Does not affect other languages.')
    );

    $form['search_pages'] = array(
      '#type' => 'details',
      '#title' => $this->t('Search pages'),
    );
    $form['search_pages']['add_page'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('container-inline'),
      ),
      '#attached' => array(
        'css' => array(
          drupal_get_path('module', 'search') . '/css/search.admin.css',
        ),
      ),
    );
    // In order to prevent validation errors for the parent form, this cannot be
    // required, see self::validateAddSearchPage().
    $form['search_pages']['add_page']['search_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Search page type'),
      '#empty_option' => $this->t('- Choose page type -'),
      '#options' => array_map(function ($definition) {
        return $definition['title'];
      }, $this->searchManager->getDefinitions()),
    );
    $form['search_pages']['add_page']['add_search_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add new page'),
      '#validate' => array(array($this, 'validateAddSearchPage')),
      '#submit' => array(array($this, 'submitAddSearchPage')),
      '#limit_validation_errors' => array(array('search_type')),
    );

    // Move the listing into the search_pages element.
    $form['search_pages'][$this->entitiesKey] = $form[$this->entitiesKey];
    $form['search_pages'][$this->entitiesKey]['#empty'] = $this->t('No search pages have been configured.');
    unset($form[$this->entitiesKey]);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    /** @var $entity \Drupal\search\SearchPageInterface */
    $operations = parent::getOperations($entity);

    // Prevent the default search from being disabled or deleted.
    if ($entity->isDefaultSearch()) {
      unset($operations['disable'], $operations['delete']);
    }
    else {
      $operations['default'] = array(
        'title' => $this->t('Set as default'),
        'route_name' => 'search.set_default',
        'route_parameters' => array(
          'search_page' => $entity->id(),
        ),
        'weight' => 50,
      );
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    parent::submitForm($form, $form_state);

    $search_settings = $this->configFactory->get('search.settings');
    // If these settings change, the index needs to be rebuilt.
    if (($search_settings->get('index.minimum_word_size') != $form_state['values']['minimum_word_size']) || ($search_settings->get('index.overlap_cjk') != $form_state['values']['overlap_cjk'])) {
      $search_settings->set('index.minimum_word_size', $form_state['values']['minimum_word_size']);
      $search_settings->set('index.overlap_cjk', $form_state['values']['overlap_cjk']);
      drupal_set_message($this->t('The index will be rebuilt.'));
      search_reindex();
    }

    $search_settings
      ->set('index.cron_limit', $form_state['values']['cron_limit'])
      ->save();

    drupal_set_message($this->t('The configuration options have been saved.'));
  }

  /**
   * Form submission handler for the reindex button on the search admin settings
   * form.
   */
  public function searchAdminReindexSubmit(array &$form, array &$form_state) {
    // Send the user to the confirmation page.
    $form_state['redirect_route']['route_name'] = 'search.reindex_confirm';
  }

  /**
   * Form validation handler for adding a new search page.
   */
  public function validateAddSearchPage(array &$form, array &$form_state) {
    if (empty($form_state['values']['search_type'])) {
      $this->formBuilder()->setErrorByName('search_type', $form_state, $this->t('You must select the new search page type.'));
    }
  }

  /**
   * Form submission handler for adding a new search page.
   */
  public function submitAddSearchPage(array &$form, array &$form_state) {
    $form_state['redirect_route'] = array(
      'route_name' => 'search.add_type',
      'route_parameters' => array(
        'search_plugin_id' => $form_state['values']['search_type'],
      ),
    );
  }

}
