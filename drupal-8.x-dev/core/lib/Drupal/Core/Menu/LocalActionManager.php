<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\LocalActionManager.
 */

namespace Drupal\Core\Menu;

use Drupal\Core\Access\AccessManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\LocalActionInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Manages discovery and instantiation of menu local action plugins.
 *
 * Menu local actions are links that lead to actions like "add new". The plugin
 * format allows them (if needed) to dynamically generate a title or the path
 * they link to. The annotation on the plugin provides the default title,
 * and the list of routes where the action should be rendered.
 */
class LocalActionManager extends DefaultPluginManager {

  /**
   * Provides some default values for all local action plugins.
   *
   * @var array
   */
  protected $defaults = array(
    // The plugin id. Set by the plugin system based on the top-level YAML key.
    'id' => NULL,
    // The static title for the local action.
    'title' => '',
    // The weight of the local action.
    'weight' => NULL,
    // (Required) the route name used to generate a link.
    'route_name' => NULL,
    // Default route parameters for generating links.
    'route_parameters' => array(),
    // Associative array of link options.
    'options' => array(),
    // The route names where this local action appears.
    'appears_on' => array(),
    // Default class for local action implementations.
    'class' => 'Drupal\Core\Menu\LocalActionDefault',
  );

  /**
   * A controller resolver object.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The route provider to load routes by name.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManager
   */
  protected $accessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

/**
   * The plugin instances.
   *
   * @var \Drupal\Core\Menu\LocalActionInterface[]
   */
  protected $instances = array();

  /**
   * Constructs a LocalActionManager object.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $controller_resolver
   *   An object to use in introspecting route methods.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object to use for building titles and paths for plugin
   *   instances.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Access\AccessManager $access_manager
   *   The access manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(ControllerResolverInterface $controller_resolver, Request $request, RouteProviderInterface $route_provider, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend, LanguageManagerInterface $language_manager, AccessManager $access_manager, AccountInterface $account) {
    // Skip calling the parent constructor, since that assumes annotation-based
    // discovery.
    $this->discovery = new YamlDiscovery('local_actions', $module_handler->getModuleDirectories());
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new ContainerFactory($this);
    $this->routeProvider = $route_provider;
    $this->accessManager = $access_manager;
    $this->account = $account;
    $this->controllerResolver = $controller_resolver;
    $this->request = $request;
    $this->alterInfo($module_handler, 'menu_local_actions');
    $this->setCacheBackend($cache_backend, $language_manager, 'local_action_plugins', array('local_action' => TRUE));

  }

  /**
   * Gets the title for a local action.
   *
   * @param \Drupal\Core\Menu\LocalActionInterface $local_action
   *   An object to get the title from.
   *
   * @return string
   *   The title (already localized).
   *
   * @throws \BadMethodCallException
   *   If the plugin does not implement the getTitle() method.
   */
  public function getTitle(LocalActionInterface $local_action) {
    $controller = array($local_action, 'getTitle');
    $arguments = $this->controllerResolver->getArguments($this->request, $controller);
    return call_user_func_array($controller, $arguments);
  }

  /**
   * Finds all local actions that appear on a named route.
   *
   * @param string $route_appears
   *   The route name for which to find local actions.
   *
   * @return array
   *   An array of link render arrays.
   */
  public function getActionsForRoute($route_appears) {
    if (!isset($this->instances[$route_appears])) {
      $route_names = array();
      $this->instances[$route_appears] = array();
      // @todo - optimize this lookup by compiling or caching.
      foreach ($this->getDefinitions() as $plugin_id => $action_info) {
        if (in_array($route_appears, $action_info['appears_on'])) {
          $plugin = $this->createInstance($plugin_id);
          $route_names[] = $plugin->getRouteName();
          $this->instances[$route_appears][$plugin_id] = $plugin;
        }
      }
      // Pre-fetch all the action route objects. This reduces the number of SQL
      // queries that would otherwise be triggered by the access manager.
      if (!empty($route_names)) {
        $this->routeProvider->getRoutesByNames($route_names);
      }
    }
    $links = array();
    foreach ($this->instances[$route_appears] as $plugin_id => $plugin) {
      $route_name = $plugin->getRouteName();
      $route_parameters = $plugin->getRouteParameters($this->request);
      $links[$plugin_id] = array(
        '#theme' => 'menu_local_action',
        '#link' => array(
          'title' => $this->getTitle($plugin),
          'route_name' => $route_name,
          'route_parameters' => $route_parameters,
          'localized_options' => $plugin->getOptions($this->request),
        ),
        '#access' => $this->accessManager->checkNamedRoute($route_name, $route_parameters, $this->account),
        '#weight' => $plugin->getWeight(),
      );
    }
    return $links;
  }

}
