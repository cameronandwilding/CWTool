<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityManager.
 */

namespace Drupal\Core\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\String;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\InfoHookDecorator;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages entity type plugin definitions.
 *
 * Each entity type definition array is set in the entity type's
 * annotation and altered by hook_entity_info_alter().
 *
 * The defaults for the plugin definition are provided in
 * \Drupal\Core\Entity\EntityManagerInterface::defaults.
 *
 * @see \Drupal\Core\Entity\Annotation\EntityType
 * @see \Drupal\Core\Entity\EntityInterface
 * @see \Drupal\Core\Entity\EntityTypeInterface
 * @see hook_entity_info_alter()
 */
class EntityManager extends PluginManagerBase implements EntityManagerInterface {

  /**
   * The injection container that should be passed into the controller factory.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Contains instantiated controllers keyed by controller type and entity type.
   *
   * @var array
   */
  protected $controllers = array();

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The cache backend to use.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * An array of field information per entity type, i.e. containing definitions.
   *
   * @var array
   *
   * @see hook_entity_field_info()
   */
  protected $entityFieldInfo;

  /**
   * Static cache of field definitions per bundle and entity type.
   *
   * @var array
   */
  protected $fieldDefinitions;

  /**
   * The root paths.
   *
   * @see self::__construct().
   *
   * @var \Traversable
   */
  protected $namespaces;

  /**
   * The string translationManager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationManager;

  /**
   * Static cache of bundle information.
   *
   * @var array
   */
  protected $bundleInfo;

  /**
   * Constructs a new Entity plugin manager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this object should use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The string translationManager.
   */
  public function __construct(\Traversable $namespaces, ContainerInterface $container, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, LanguageManager $language_manager, TranslationInterface $translation_manager) {
    // Allow the plugin definition to be altered by hook_entity_info_alter().

    $this->moduleHandler = $module_handler;
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->namespaces = $namespaces;
    $this->translationManager = $translation_manager;

    $this->discovery = new AnnotatedClassDiscovery('Entity', $namespaces, 'Drupal\Core\Entity\Annotation\EntityType');
    $this->discovery = new InfoHookDecorator($this->discovery, 'entity_info');
    $this->discovery = new AlterDecorator($this->discovery, 'entity_info');
    $this->discovery = new CacheDecorator($this->discovery, 'entity_info:' . $this->languageManager->getCurrentLanguage()->id, 'cache', Cache::PERMANENT, array('entity_info' => TRUE));

    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    parent::clearCachedDefinitions();

    $this->bundleInfo = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($entity_type_id, $exception_on_invalid = FALSE) {
    if (($entity_type = parent::getDefinition($entity_type_id)) && class_exists($entity_type->getClass())) {
      return $entity_type;
    }
    elseif (!$exception_on_invalid) {
      return NULL;
    }

    throw new PluginNotFoundException($entity_type_id, sprintf('The "%s" entity type does not exist.', $entity_type_id));
  }

  /**
   * {@inheritdoc}
   */
  public function hasController($entity_type, $controller_type) {
    if ($definition = $this->getDefinition($entity_type)) {
      return $definition->hasControllerClass($controller_type);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageController($entity_type) {
    return $this->getController($entity_type, 'storage', 'getStorageClass');
  }

  /**
   * {@inheritdoc}
   */
  public function getListController($entity_type) {
    return $this->getController($entity_type, 'list', 'getListClass');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormController($entity_type, $operation) {
    if (!isset($this->controllers['form'][$operation][$entity_type])) {
      if (!$class = $this->getDefinition($entity_type, TRUE)->getFormClass($operation)) {
        throw new InvalidPluginDefinitionException($entity_type, sprintf('The "%s" entity type did not specify a "%s" form class.', $entity_type, $operation));
      }
      if (in_array('Drupal\Core\DependencyInjection\ContainerInjectionInterface', class_implements($class))) {
        $controller = $class::create($this->container);
      }
      else {
        $controller = new $class();
      }

      $controller
        ->setTranslationManager($this->translationManager)
        ->setModuleHandler($this->moduleHandler)
        ->setOperation($operation);
      $this->controllers['form'][$operation][$entity_type] = $controller;
    }
    return $this->controllers['form'][$operation][$entity_type];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type) {
    return $this->getController($entity_type, 'view_builder', 'getViewBuilderClass');
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessController($entity_type) {
    return $this->getController($entity_type, 'access', 'getAccessClass');
  }

  /**
   * Creates a new controller instance.
   *
   * @param string $entity_type
   *   The entity type for this controller.
   * @param string $controller_type
   *   The controller type to create an instance for.
   * @param string $controller_class_getter
   *   (optional) The method to call on the entity type object to get the controller class.
   *
   * @return mixed
   *   A controller instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getController($entity_type, $controller_type, $controller_class_getter = NULL) {
    if (!isset($this->controllers[$controller_type][$entity_type])) {
      $definition = $this->getDefinition($entity_type, TRUE);
      if ($controller_class_getter) {
        $class = $definition->{$controller_class_getter}();
      }
      else {
        $class = $definition->getControllerClass($controller_type);
      }
      if (!$class) {
        throw new InvalidPluginDefinitionException($entity_type, sprintf('The "%s" entity type did not specify a %s class.', $entity_type, $controller_type));
      }
      if (is_subclass_of($class, 'Drupal\Core\Entity\EntityControllerInterface')) {
        $controller = $class::createInstance($this->container, $definition);
      }
      else {
        $controller = new $class($definition);
      }
      if (method_exists($controller, 'setModuleHandler')) {
        $controller->setModuleHandler($this->moduleHandler);
      }
      if (method_exists($controller, 'setTranslationManager')) {
        $controller->setTranslationManager($this->translationManager);
      }
      $this->controllers[$controller_type][$entity_type] = $controller;
    }
    return $this->controllers[$controller_type][$entity_type];
  }

  /**
   * {@inheritdoc}
   */
  public function getAdminRouteInfo($entity_type_id, $bundle) {
    if (($entity_type = $this->getDefinition($entity_type_id)) && $admin_form = $entity_type->getLinkTemplate('admin-form')) {
      return array(
        'route_name' => $admin_form,
        'route_parameters' => array(
          $entity_type->getBundleEntityType() => $bundle,
        ),
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitions($entity_type_id, $bundle = NULL) {
    if (!isset($this->entityFieldInfo[$entity_type_id])) {
      // First, try to load from cache.
      $cid = 'entity_field_definitions:' . $entity_type_id . ':' . $this->languageManager->getCurrentLanguage()->id;
      if ($cache = $this->cache->get($cid)) {
        $this->entityFieldInfo[$entity_type_id] = $cache->data;
      }
      else {
        // @todo: Refactor to allow for per-bundle overrides.
        // See https://drupal.org/node/2114707.
        $entity_type = $this->getDefinition($entity_type_id);
        $class = $entity_type->getClass();

        $base_definitions = $class::baseFieldDefinitions($entity_type_id);
        foreach ($base_definitions as &$base_definition) {
          $base_definition->setTargetEntityTypeId($entity_type_id);
        }
        $this->entityFieldInfo[$entity_type_id] = array(
          'definitions' => $base_definitions,
          // Contains definitions of optional (per-bundle) fields.
          'optional' => array(),
          // An array keyed by bundle name containing the optional fields added
          // by the bundle.
          'bundle map' => array(),
        );

        // Invoke hooks.
        $result = $this->moduleHandler->invokeAll($entity_type_id . '_field_info');
        $this->entityFieldInfo[$entity_type_id] = NestedArray::mergeDeep($this->entityFieldInfo[$entity_type_id], $result);
        $result = $this->moduleHandler->invokeAll('entity_field_info', array($entity_type_id));
        $this->entityFieldInfo[$entity_type_id] = NestedArray::mergeDeep($this->entityFieldInfo[$entity_type_id], $result);

        // Automatically set the field name for non-configurable fields.
        foreach (array('definitions', 'optional') as $key) {
          foreach ($this->entityFieldInfo[$entity_type_id][$key] as $field_name => &$definition) {
            if ($definition instanceof FieldDefinition) {
              $definition->setName($field_name);
            }
          }
        }

        // Invoke alter hooks.
        $hooks = array('entity_field_info', $entity_type_id . '_field_info');
        $this->moduleHandler->alter($hooks, $this->entityFieldInfo[$entity_type_id], $entity_type_id);

        // Ensure all basic fields are not defined as translatable.
        $keys = array_intersect_key(array_filter($entity_type->getKeys()), array_flip(array('id', 'revision', 'uuid', 'bundle')));
        $untranslatable_fields = array_flip(array('langcode') + $keys);
        foreach (array('definitions', 'optional') as $key) {
          foreach ($this->entityFieldInfo[$entity_type_id][$key] as $field_name => &$definition) {
            if (isset($untranslatable_fields[$field_name]) && $definition->isTranslatable()) {
              throw new \LogicException(String::format('The @field field cannot be translatable.', array('@field' => $definition->getLabel())));
            }
          }
        }

        $this->cache->set($cid, $this->entityFieldInfo[$entity_type_id], Cache::PERMANENT, array('entity_info' => TRUE, 'entity_field_info' => TRUE));
      }
    }

    if (!$bundle) {
      return $this->entityFieldInfo[$entity_type_id]['definitions'];
    }
    else {
      // Add in per-bundle fields.
      if (!isset($this->fieldDefinitions[$entity_type_id][$bundle])) {
        $this->fieldDefinitions[$entity_type_id][$bundle] = $this->entityFieldInfo[$entity_type_id]['definitions'];
        if (isset($this->entityFieldInfo[$entity_type_id]['bundle map'][$bundle])) {
          $this->fieldDefinitions[$entity_type_id][$bundle] += array_intersect_key($this->entityFieldInfo[$entity_type_id]['optional'], array_flip($this->entityFieldInfo[$entity_type_id]['bundle map'][$bundle]));
        }
      }
      return $this->fieldDefinitions[$entity_type_id][$bundle];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitionsByConstraints($entity_type, array $constraints) {
    // @todo: Add support for specifying multiple bundles.
    return $this->getFieldDefinitions($entity_type, isset($constraints['Bundle']) ? $constraints['Bundle'] : NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedFieldDefinitions() {
    unset($this->entityFieldInfo);
    unset($this->fieldDefinitions);
    Cache::deleteTags(array('entity_field_info' => TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleInfo($entity_type) {
    $bundle_info = $this->getAllBundleInfo();
    return isset($bundle_info[$entity_type]) ? $bundle_info[$entity_type] : array();
  }

  /**
   * {@inheritdoc}
   */
  public function getAllBundleInfo() {
    if (!isset($this->bundleInfo)) {
      $langcode = $this->languageManager->getCurrentLanguage()->id;
      if ($cache = $this->cache->get("entity_bundle_info:$langcode")) {
        $this->bundleInfo = $cache->data;
      }
      else {
        $this->bundleInfo = $this->moduleHandler->invokeAll('entity_bundle_info');
        // If no bundles are provided, use the entity type name and label.
        foreach ($this->getDefinitions() as $type => $entity_type) {
          if (!isset($this->bundleInfo[$type])) {
            $this->bundleInfo[$type][$type]['label'] = $entity_type->getLabel();
          }
        }
        $this->moduleHandler->alter('entity_bundle_info', $this->bundleInfo);
        $this->cache->set("entity_bundle_info:$langcode", $this->bundleInfo, Cache::PERMANENT, array('entity_info' => TRUE));
      }
    }

    return $this->bundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeLabels() {
    $options = array();
    foreach ($this->getDefinitions() as $entity_type => $definition) {
      $options[$entity_type] = $definition->getLabel();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getTranslationFromContext(EntityInterface $entity, $langcode = NULL, $context = array()) {
    $translation = $entity;

    if ($entity instanceof TranslatableInterface) {
      if (empty($langcode)) {
        $langcode = $this->languageManager->getCurrentLanguage(Language::TYPE_CONTENT)->id;
      }

      // Retrieve language fallback candidates to perform the entity language
      // negotiation.
      $context['data'] = $entity;
      $context += array('operation' => 'entity_view');
      $candidates = $this->languageManager->getFallbackCandidates($langcode, $context);

      // Ensure the default language has the proper language code.
      $default_language = $entity->getUntranslated()->language();
      $candidates[$default_language->id] = Language::LANGCODE_DEFAULT;

      // Return the most fitting entity translation.
      foreach ($candidates as $candidate) {
        if ($entity->hasTranslation($candidate)) {
          $translation = $entity->getTranslation($candidate);
          break;
        }
      }
    }

    return $translation;
  }

}
