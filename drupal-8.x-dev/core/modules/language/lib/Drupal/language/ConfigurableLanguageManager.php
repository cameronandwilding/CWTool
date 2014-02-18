<?php

/**
 * @file
 * Contains \Drupal\language\ConfigurableLanguageManager.
 */

namespace Drupal\language;

use Drupal\Component\PhpStorage\PhpStorageFactory;
use Drupal\Component\Utility\MapArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overrides default LanguageManager to provide configured languages.
 */
class ConfigurableLanguageManager extends LanguageManager implements ConfigurableLanguageManagerInterface {

  /**
   * The configuration storage service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $negotiator;

  /**
   * Local cache for language type configuration data.
   *
   * @var array
   */
  protected $languageTypes;

  /**
   * Local cache for language type information.
   *
   * @var array
   */
  protected $languageTypesInfo;

  /**
   * An array of language objects keyed by language type.
   *
   * @var array
   */
  protected $negotiatedLanguages;

  /**
   * Whether or not the language manager has been initialized.
   *
   * @var bool
   */
  protected $initialized = FALSE;

  /**
   * Whether already in the process of language initialization.
   *
   * @var bool
   */
  protected $initializing = FALSE;

  /**
   * Rebuild the container to register services needed on multilingual sites.
   */
  public static function rebuildServices() {
    PhpStorageFactory::get('service_container')->deleteAll();
  }

  /**
   * Constructs a new ConfigurableLanguageManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration storage service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(LanguageDefault $default_language, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->defaultLanguage = $default_language;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    if (!$this->initialized) {
      foreach ($this->getDefinedLanguageTypes() as $type) {
        $this->getCurrentLanguage($type);
      }
      $this->initialized = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isMultilingual() {
    return count($this->getLanguages(Language::STATE_CONFIGURABLE)) > 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageTypes() {
    $this->loadLanguageTypesConfiguration();
    return $this->languageTypes['configurable'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedLanguageTypes() {
    $this->loadLanguageTypesConfiguration();
    return $this->languageTypes['all'];
  }

  /**
   * Retrieves language types from the configuration storage.
   *
   * @return array
   *   An array of language type names.
   */
  protected function loadLanguageTypesConfiguration() {
    if (!$this->languageTypes) {
      $this->languageTypes = $this->configFactory->get('language.types')->get() ?: array('configurable' => array(), 'all' => parent::getLanguageTypes());
    }
    return $this->languageTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedLanguageTypesInfo() {
    if (!isset($this->languageTypesInfo)) {
      $info = $this->moduleHandler->invokeAll('language_types_info');
      // Let other modules alter the list of language types.
      $this->moduleHandler->alter('language_types_info', $info);
      $this->languageTypesInfo = $info;
    }
    return $this->languageTypesInfo;
  }

  /**
   * Stores language types configuration.
   */
  public function saveLanguageTypesConfiguration(array $values) {
    $config = $this->configFactory->get('language.types');
    if (isset($values['configurable'])) {
      $config->set('configurable', $values['configurable']);
    }
    if (isset($values['all'])) {
      $config->set('all', $values['all']);
    }
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentLanguage($type = Language::TYPE_INTERFACE) {
    if (!isset($this->negotiatedLanguages[$type])) {
      // Ensure we have a valid value for this language type.
      $this->negotiatedLanguages[$type] = $this->getDefaultLanguage();

      if ($this->negotiator && $this->isMultilingual()) {
        if (!$this->initializing) {
          $this->initializing = TRUE;
          $this->negotiatedLanguages[$type] = $this->negotiator->initializeType($type);
          $this->initializing = FALSE;
        }
        // If the current interface language needs to be retrieved during
        // initialization we return the system language. This way string
        // translation calls happening during initialization will return the
        // original strings which can be translated by calling them again
        // afterwards. This can happen for instance while parsing negotiation
        // method definitions.
        elseif ($type == Language::TYPE_INTERFACE) {
          return new Language(array('id' => Language::LANGCODE_SYSTEM));
        }
      }
    }

    return $this->negotiatedLanguages[$type];
  }

  /**
   * {@inheritdoc}
   */
  public function reset($type = NULL) {
    if (!isset($type)) {
      $this->initialized = FALSE;
      $this->negotiatedLanguages = array();
      $this->languageTypes = NULL;
      $this->languageTypesInfo = NULL;
      $this->languages = NULL;
      if ($this->negotiator) {
        $this->negotiator->reset();
      }
    }
    elseif (isset($this->negotiatedLanguages[$type])) {
      unset($this->negotiatedLanguages[$type]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public function getNegotiator() {
    return $this->negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public function setNegotiator(LanguageNegotiatorInterface $negotiator) {
    $this->negotiator = $negotiator;
    $this->initialized = FALSE;
    $this->negotiatedLanguages = array();
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguages($flags = Language::STATE_CONFIGURABLE) {
    if (!isset($this->languages)) {
      // Prepopulate the language list with the default language to keep things
      // working even if we have no configuration.
      $default = $this->getDefaultLanguage();
      $this->languages = array($default->id => $default);

      // Retrieve the config storage to list available languages.
      $prefix = 'language.entity.';
      $storage = $this->configFactory->get($prefix . Language::LANGCODE_NOT_SPECIFIED)->getStorage();
      $config_ids = $storage->listAll($prefix);

      // Instantiate languages from config objects.
      $weight = 0;
      foreach ($this->configFactory->loadMultiple($config_ids) as $config) {
        $data = $config->get();
        $langcode = $data['id'];
        // Initialize default property so callers have an easy reference and can
        // save the same object without data loss.
        $data['default'] = ($langcode == $default->id);
        $data['name'] = $data['label'];
        $this->languages[$langcode] = new Language($data);
        $weight = max(array($weight, $this->languages[$langcode]->weight));
      }

      // Add locked languages, they will be filtered later if needed.
      $this->languages += $this->getDefaultLockedLanguages($weight);

      // Sort the language list by weight.
      Language::sort($this->languages);
    }

    return parent::getLanguages($flags);
  }

  /**
   * {@inheritdoc}
   */
  public function updateLockedLanguageWeights() {
    $max_weight = 0;

    // Get maximum weight to update the system languages to keep them on bottom.
    foreach ($this->getLanguages(Language::STATE_CONFIGURABLE) as $language) {
      if (!$language->locked && $language->weight > $max_weight) {
        $max_weight = $language->weight;
      }
    }

    // Loop locked languages to maintain the existing order.
    $locked_languages = $this->getLanguages(Language::STATE_LOCKED);
    $config_ids = array_map(function($language) { return 'language.entity.' . $language->id; }, $locked_languages);
    foreach ($this->configFactory->loadMultiple($config_ids) as $config_id => $config) {
      // Update system languages weight.
      $max_weight++;
      $config->set('weight', $max_weight);
      $config->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackCandidates($langcode = NULL, array $context = array()) {
    if ($this->isMultilingual()) {
      // Get languages ordered by weight, add Language::LANGCODE_NOT_SPECIFIED
      // at the end.
      $candidates = array_keys($this->getLanguages());
      $candidates[] = Language::LANGCODE_NOT_SPECIFIED;
      $candidates = MapArray::copyValuesToKeys($candidates);

      // The first candidate should always be the desired language if specified.
      if (!empty($langcode)) {
        $candidates = array($langcode => $langcode) + $candidates;
      }

      // Let other modules hook in and add/change candidates.
      $type = 'language_fallback_candidates';
      $types = array();
      if (!empty($context['operation'])) {
        $types[] = $type . '_' .  $context['operation'];
      }
      $types[] = $type;
      $this->moduleHandler->alter($types, $candidates, $context);
    }
    else {
      $candidates = parent::getFallbackCandidates($langcode, $context);
    }

    return $candidates;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageSwitchLinks($type, $path) {
    $links = FALSE;

    if ($this->negotiator) {
      foreach ($this->negotiator->getNegotiationMethods($type) as $method_id => $method) {
        $reflector = new \ReflectionClass($method['class']);

        if ($reflector->implementsInterface('\Drupal\language\LanguageSwitcherInterface')) {
          $result = $this->negotiator->getNegotiationMethodInstance($method_id)->getLanguageSwitchLinks($this->request, $type, $path);

          if (!empty($result)) {
            // Allow modules to provide translations for specific links.
            $this->moduleHandler->alter('language_switch_links', $result, $type, $path);
            $links = (object) array('links' => $result, 'method_id' => $method_id);
            break;
          }
        }
      }
    }

    return $links;
  }

}
