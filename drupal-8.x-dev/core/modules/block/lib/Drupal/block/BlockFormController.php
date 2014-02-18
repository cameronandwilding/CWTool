<?php

/**
 * @file
 * Contains \Drupal\block\BlockFormController.
 */

namespace Drupal\block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormController;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides form controller for block instance forms.
 */
class BlockFormController extends EntityFormController {

  /**
   * The block entity.
   *
   * @var \Drupal\block\BlockInterface
   */
  protected $entity;

  /**
   * The block storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storageController;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a BlockFormController object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityManagerInterface $entity_manager, QueryFactory $entity_query_factory, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory) {
    $this->storageController = $entity_manager->getStorageController('block');
    $this->entityQueryFactory = $entity_query_factory;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity.query'),
      $container->get('language_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $entity = $this->entity;
    $form['#tree'] = TRUE;
    $form['settings'] = $entity->getPlugin()->buildConfigurationForm(array(), $form_state);

    // If creating a new block, calculate a safe default machine name.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this block instance. Must be alpha-numeric and underscore separated.'),
      '#default_value' => !$entity->isNew() ? $entity->id() : $this->getUniqueMachineName($entity),
      '#machine_name' => array(
        'exists' => 'block_load',
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => array('settings', 'label'),
      ),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    );

    // Visibility settings.
    $form['visibility'] = array(
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility settings'),
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'block') . '/block.js'),
      ),
      '#tree' => TRUE,
      '#weight' => 10,
      '#parents' => array('visibility'),
    );

    // Per-path visibility.
    $form['visibility']['path'] = array(
      '#type' => 'details',
      '#title' => $this->t('Pages'),
      '#collapsed' => TRUE,
      '#group' => 'visibility',
      '#weight' => 0,
    );

    // @todo remove this access check and inject it in some other way. In fact
    //   this entire visibility settings section probably needs a separate user
    //   interface in the near future.
    $visibility = $entity->get('visibility');
    $access = $this->currentUser()->hasPermission('use PHP for settings');
    if (!empty($visibility['path']['visibility']) && $visibility['path']['visibility'] == BLOCK_VISIBILITY_PHP && !$access) {
      $form['visibility']['path']['visibility'] = array(
        '#type' => 'value',
        '#value' => BLOCK_VISIBILITY_PHP,
      );
      $form['visibility']['path']['pages'] = array(
        '#type' => 'value',
        '#value' => !empty($visibility['path']['pages']) ? $visibility['path']['pages'] : '',
      );
    }
    else {
      $options = array(
        BLOCK_VISIBILITY_NOTLISTED => $this->t('All pages except those listed'),
        BLOCK_VISIBILITY_LISTED => $this->t('Only the listed pages'),
      );
      $description = $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %user for the current user's page and %user-wildcard for every user page. %front is the front page.", array('%user' => 'user', '%user-wildcard' => 'user/*', '%front' => '<front>'));

      $form['visibility']['path']['visibility'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Show block on specific pages'),
        '#options' => $options,
        '#default_value' => !empty($visibility['path']['visibility']) ? $visibility['path']['visibility'] : BLOCK_VISIBILITY_NOTLISTED,
      );
      $form['visibility']['path']['pages'] = array(
        '#type' => 'textarea',
        '#title' => '<span class="visually-hidden">' . $this->t('Pages') . '</span>',
        '#default_value' => !empty($visibility['path']['pages']) ? $visibility['path']['pages'] : '',
        '#description' => $description,
      );
    }

    // Configure the block visibility per language.
    if ($this->languageManager->isMultilingual() && $this->languageManager instanceof ConfigurableLanguageManagerInterface) {
      $language_types = $this->languageManager->getLanguageTypes();

      // Fetch languages.
      $languages = $this->languageManager->getLanguages(Language::STATE_ALL);
      $langcodes_options = array();
      foreach ($languages as $language) {
        // @todo $language->name is not wrapped with t(), it should be replaced
        //   by CMI translation implementation.
        $langcodes_options[$language->id] = $language->name;
      }
      $form['visibility']['language'] = array(
        '#type' => 'details',
        '#title' => $this->t('Languages'),
        '#collapsed' => TRUE,
        '#group' => 'visibility',
        '#weight' => 5,
      );
      // If there are multiple configurable language types, let the user pick
      // which one should be applied to this visibility setting. This way users
      // can limit blocks by interface language or content language for example.
      $info = $this->languageManager->getDefinedLanguageTypesInfo();
      $language_type_options = array();
      foreach ($language_types as $type_key) {
        $language_type_options[$type_key] = $info[$type_key]['name'];
      }
      $form['visibility']['language']['language_type'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Language type'),
        '#options' => $language_type_options,
        '#default_value' => !empty($visibility['language']['language_type']) ? $visibility['language']['language_type'] : reset($language_types),
        '#access' => count($language_type_options) > 1,
      );
      $form['visibility']['language']['langcodes'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Show this block only for specific languages'),
        '#default_value' => !empty($visibility['language']['langcodes']) ? $visibility['language']['langcodes'] : array(),
        '#options' => $langcodes_options,
        '#description' => $this->t('Show this block only for the selected language(s). If you select no languages, the block will be visible in all languages.'),
      );
    }

    // Per-role visibility.
    $role_options = array_map('check_plain', user_role_names());
    $form['visibility']['role'] = array(
      '#type' => 'details',
      '#title' => $this->t('Roles'),
      '#collapsed' => TRUE,
      '#group' => 'visibility',
      '#weight' => 10,
    );
    $form['visibility']['role']['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Show block for specific roles'),
      '#default_value' => !empty($visibility['role']['roles']) ? $visibility['role']['roles'] : array(),
      '#options' => $role_options,
      '#description' => $this->t('Show this block only for the selected role(s). If you select no roles, the block will be visible to all users.'),
    );

    // Theme settings.
    if ($theme = $entity->get('theme')) {
      $form['theme'] = array(
        '#type' => 'value',
        '#value' => $entity->get('theme'),
      );
    }
    else {
      $theme_options = array();
      foreach (list_themes() as $theme_name => $theme_info) {
        if (!empty($theme_info->status)) {
          $theme_options[$theme_name] = $theme_info->info['name'];
        }
      }
      $theme = $this->configFactory->get('system.theme')->get('default');
      $form['theme'] = array(
        '#type' => 'select',
        '#options' => $theme_options,
        '#title' => t('Theme'),
        '#default_value' => $theme,
        '#ajax' => array(
          'callback' => array($this, 'themeSwitch'),
          'wrapper' => 'edit-block-region-wrapper',
        ),
      );
    }
    // Region settings.
    $form['region'] = array(
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#description' => $this->t('Select the region where this block should be displayed.'),
      '#default_value' => $entity->get('region'),
      '#empty_value' => BlockInterface::BLOCK_REGION_NONE,
      '#options' => system_region_list($theme, REGIONS_VISIBLE),
      '#prefix' => '<div id="edit-block-region-wrapper">',
      '#suffix' => '</div>',
    );
    $form['#attached']['css'] = array(
      drupal_get_path('module', 'block') . '/css/block.admin.css',
    );
    return $form;
  }

  /**
   * Handles switching the available regions based on the selected theme.
   */
  public function themeSwitch($form, &$form_state) {
    $form['region']['#options'] = system_region_list($form_state['values']['theme'], REGIONS_VISIBLE);
    return $form['region'];
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save block');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

    // Remove empty lines from the role visibility list.
    $form_state['values']['visibility']['role']['roles'] = array_filter($form_state['values']['visibility']['role']['roles']);
    // The Block Entity form puts all block plugin form elements in the
    // settings form element, so just pass that to the block for validation.
    $settings = array(
      'values' => &$form_state['values']['settings']
    );
    // Call the plugin validate handler.
    $this->entity->getPlugin()->validateConfigurationForm($form, $settings);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    parent::submit($form, $form_state);

    $entity = $this->entity;
    // The Block Entity form puts all block plugin form elements in the
    // settings form element, so just pass that to the block for submission.
    // @todo Find a way to avoid this manipulation.
    $settings = array(
      'values' => &$form_state['values']['settings'],
      'errors' => $form_state['errors'],
    );

    // Call the plugin submit handler.
    $entity->getPlugin()->submitConfigurationForm($form, $settings);

    // Save the settings of the plugin.
    $entity->save();

    drupal_set_message($this->t('The block configuration has been saved.'));
    // Invalidate the content cache and redirect to the block listing,
    // because we need to remove cached block contents for each cache backend.
    Cache::invalidateTags(array('content' => TRUE));
    $form_state['redirect_route'] = array(
      'route_name' => 'block.admin_display_theme',
      'route_parameters' => array(
        'theme' => $form_state['values']['theme'],
      ),
      'options' => array(
        'query' => array('block-placement' => drupal_html_class($this->entity->id()))
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    $entity = parent::buildEntity($form, $form_state);

    // visibility__active_tab is Form API state and not configuration.
    // @todo Fix vertical tabs.
    $visibility = $entity->get('visibility');
    unset($visibility['visibility__active_tab']);
    $entity->set('visibility', $visibility);

    return $entity;
  }

  /**
   * Generates a unique machine name for a block.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block entity.
   *
   * @return string
   *   Returns the unique name.
   */
  public function getUniqueMachineName(BlockInterface $block) {
    $suggestion = $block->getPlugin()->getMachineNameSuggestion();

    // Get all the blocks which starts with the suggested machine name.
    $query = $this->entityQueryFactory->get('block');
    $query->condition('id', $suggestion, 'CONTAINS');
    $block_ids = $query->execute();

    $block_ids = array_map(function ($block_id) {
      $parts = explode('.', $block_id);
      return end($parts);
    }, $block_ids);

    // Iterate through potential IDs until we get a new one. E.g.
    // 'plugin', 'plugin_2', 'plugin_3'...
    $count = 1;
    $machine_default = $suggestion;
    while (in_array($machine_default, $block_ids)) {
      $machine_default = $suggestion . '_' . ++$count;
    }
    return $machine_default;
  }

}
