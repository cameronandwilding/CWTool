<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\display\PathPluginBase.
 */

namespace Drupal\views\Plugin\views\display;

use Drupal\Core\KeyValueStore\StateInterface;
use Drupal\Core\Routing\RouteCompiler;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\views\Views;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * The base display plugin for path/callbacks. This is used for pages and feeds.
 *
 * @see \Drupal\views\EventSubscriber\RouteSubscriber
 */
abstract class PathPluginBase extends DisplayPluginBase implements DisplayRouterInterface {

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * Constructs a PathPluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\KeyValueStore\StateInterface $state
   *   The state key value store.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, RouteProviderInterface $route_provider, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeProvider = $route_provider;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('state')
    );
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::hasPath().
   */
  public function hasPath() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    $bits = explode('/', $this->getOption('path'));
    if ($this->isDefaultTabPath()) {
      array_pop($bits);
    }
    return implode('/', $bits);
  }

  /**
   * Determines if this display's path is a default tab.
   *
   * @return bool
   *   TRUE if the display path is for a default tab, FALSE otherwise.
   */
  protected function isDefaultTabPath() {
    $menu = $this->getOption('menu');
    $tab_options = $this->getOption('tab_options');
    return $menu['type'] == 'default tab' && !empty($tab_options['type']) && $tab_options['type'] != 'none';
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase:defineOptions().
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['path'] = array('default' => '');
    $options['route_name'] = array('default' => '');

    return $options;
  }

  /**
   * Generates a route entry for a given view and display.
   *
   * @param string $view_id
   *   The ID of the view.
   * @param string $display_id
   *   The current display ID.
   *
   * @return \Symfony\Component\Routing\Route
   *   The route for the view.
   */
  protected function getRoute($view_id, $display_id) {
    $defaults = array(
      '_controller' => 'Drupal\views\Routing\ViewPageController::handle',
      'view_id' => $view_id,
      'display_id' => $display_id,
    );

    // @todo How do we apply argument validation?
    $bits = explode('/', $this->getOption('path'));
    // @todo Figure out validation/argument loading.
    // Replace % with %views_arg for menu autoloading and add to the
    // page arguments so the argument actually comes through.
    $arg_counter = 0;

    $this->view->initHandlers();
    $view_arguments = (array) $this->view->argument;

    $argument_ids = array_keys($view_arguments);
    $total_arguments = count($argument_ids);

    $argument_map = array();

    // Replace arguments in the views UI (defined via %) with parameters in
    // routes (defined via {}). As a name for the parameter use arg_$key, so
    // it can be pulled in the views controller from the request.
    foreach ($bits as $pos => $bit) {
      if ($bit == '%') {
        // Generate the name of the parameter using the key of the argument
        // handler.
        $arg_id = 'arg_' . $arg_counter++;
        $bits[$pos] = '{' . $arg_id . '}';
      }
      elseif (strpos($bit, '%') === 0) {
        // Use the name defined in the path.
        $parameter_name = substr($bit, 1);
        $arg_id = 'arg_' . $arg_counter++;
        $argument_map[$arg_id] = $parameter_name;
        $bits[$pos] = '{' . $parameter_name . '}';
      }
    }

    // Add missing arguments not defined in the path, but added as handler.
    while (($total_arguments - $arg_counter) > 0) {
      $arg_id = 'arg_' . $arg_counter++;
      $bit = '{' . $arg_id . '}';
      // In contrast to the previous loop add the defaults here, as % was not
      // specified, which means the argument is optional.
      $defaults[$arg_id] = NULL;
      $bits[] = $bit;
    }

    // If this is to be a default tab, create the route for the parent path.
    if ($this->isDefaultTabPath()) {
      $bit = array_pop($bits);
      if (empty($bits)) {
        $bits[] = $bit;
      }
    }

    $route_path = '/' . implode('/', $bits);

    $route = new Route($route_path, $defaults);

    // Add access check parameters to the route.
    $access_plugin = $this->getPlugin('access');
    if (!isset($access_plugin)) {
      // @todo Do we want to support a default plugin in getPlugin itself?
      $access_plugin = Views::pluginManager('access')->createInstance('none');
    }
    $access_plugin->alterRouteDefinition($route);
    // @todo Figure out whether _access_mode ANY is the proper one. This is
    //   particular important for altering routes.
    $route->setOption('_access_mode', 'ANY');

    // Set the argument map, in order to support named parameters.
    $route->setDefault('_view_argument_map', $argument_map);

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function collectRoutes(RouteCollection $collection) {
    $view_id = $this->view->storage->id();
    $display_id = $this->display['id'];

    $route = $this->getRoute($view_id, $display_id);

    if (!($route_name = $this->getOption('route_name'))) {
      $route_name = "view.$view_id.$display_id";
    }
    $collection->add($route_name, $route);
    return array("$view_id.$display_id" => $route_name);
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $view_route_names = array();
    $view_path = $this->getPath();
    foreach ($collection->all() as $name => $route) {
      // Find all paths which match the path of the current display..
      $route_path = RouteCompiler::getPathWithoutDefaults($route);
      $route_path = RouteCompiler::getPatternOutline($route_path);
      // Ensure that we don't override a route which is already controlled by
      // views.
      if (!$route->hasDefault('view_id') && ('/' . $view_path == $route_path)) {
        $parameters = $route->compile()->getPathVariables();

        // @todo Figure out whether we need to merge some settings (like
        // requirements).

        // Replace the existing route with a new one based on views.
        $collection->remove($name);

        $view_id = $this->view->storage->id();
        $display_id = $this->display['id'];
        $route = $this->getRoute($view_id, $display_id);

        $path = $route->getPath();
        // Load the argument IDs from the view executable.
        $view_arguments = (array) $this->view->argument;
        $argument_ids = array_keys($view_arguments);

        // Replace the path with the original parameter names and add a mapping.
        $argument_map = array();
        // We assume that the numeric ids of the parameters match the one from
        // the view argument handlers.
        foreach ($parameters as $position => $parameter_name) {
          $path = str_replace('arg_' . $position, $parameter_name, $path);
          $argument_map['arg_' . $position] = $parameter_name;
        }
        // Set the corrected path and the mapping to the route object.
        $route->setDefault('_view_argument_map', $argument_map);
        $route->setPath($path);

        $collection->add($name, $route);
        $view_route_names[$view_id . '.' . $display_id] = $name;
      }
    }

    return $view_route_names;
  }

  /**
   * {@inheritdoc}
   */
  public function executeHookMenuLinkDefaults(array &$existing_links) {
    $links = array();

    // Replace % with the link to our standard views argument loader
    // views_arg_load -- which lives in views.module.

    $bits = explode('/', $this->getOption('path'));
    $page_arguments = array($this->view->storage->id(), $this->display['id']);
    $this->view->initHandlers();
    $view_arguments = $this->view->argument;

    // Replace % with %views_arg for menu autoloading and add to the
    // page arguments so the argument actually comes through.
    foreach ($bits as $pos => $bit) {
      if ($bit == '%') {
        // If a view requires any arguments we cannot create a static menu link.
        return array();
      }
    }

    $view_route_names = $this->state->get('views.view_route_names') ?: array();

    $path = implode('/', $bits);
    $menu_link_id = 'views.' . str_replace('/', '.', $path);

    if ($path) {
      $menu = $this->getOption('menu');
      if (!empty($menu['type']) && $menu['type'] == 'normal') {
        $links[$menu_link_id] = array();
        // Some views might override existing paths, so we have to set the route
        // name based upon the altering.
        $view_id_display =  "{$this->view->storage->id()}.{$this->display['id']}";
        $links[$menu_link_id] = array(
          'route_name' => isset($view_route_names[$view_id_display]) ? $view_route_names[$view_id_display] : "view.$view_id_display",
          // Identify URL embedded arguments and correlate them to a handler.
          'load arguments'  => array($this->view->storage->id(), $this->display['id'], '%index'),
          'machine_name' => $menu_link_id,
        );
        $links[$menu_link_id]['link_title'] = $menu['title'];
        $links[$menu_link_id]['description'] = $menu['description'];

        if (isset($menu['weight'])) {
          $links[$menu_link_id]['weight'] = intval($menu['weight']);
        }

        // Insert item into the proper menu.
        $links[$menu_link_id]['menu_name'] = $menu['name'];
      }
    }

    return $links;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::executeHookMenu().
   */
  public function executeHookMenu($callbacks) {
    $items = array();
    // Replace % with the link to our standard views argument loader
    // views_arg_load -- which lives in views.module.

    $bits = explode('/', $this->getOption('path'));
    $page_arguments = array($this->view->storage->id(), $this->display['id']);
    $this->view->initHandlers();
    $view_arguments = $this->view->argument;

    $path = implode('/', $bits);

    $view_route_names = $this->state->get('views.view_route_names') ?: array();

    if ($path) {
      // Some views might override existing paths, so we have to set the route
      // name based upon the altering.
      $view_id_display =  "{$this->view->storage->id()}.{$this->display['id']}";
      $items[$path] = array(
        'route_name' => isset($view_route_names[$view_id_display]) ? $view_route_names[$view_id_display] : "view.$view_id_display",
        // Identify URL embedded arguments and correlate them to a handler.
        'load arguments'  => array($this->view->storage->id(), $this->display['id'], '%index'),
      );
      $menu = $this->getOption('menu');
      if (empty($menu)) {
        $menu = array('type' => 'none');
      }
      // Set the title and description if we have one.
      if ($menu['type'] != 'none') {
        $items[$path]['title'] = $menu['title'];
        $items[$path]['description'] = $menu['description'];
      }

      if (isset($menu['weight'])) {
        $items[$path]['weight'] = intval($menu['weight']);
      }

      switch ($menu['type']) {
        case 'none':
        default:
          $items[$path]['type'] = MENU_CALLBACK;
          break;
        case 'normal':
          $items[$path]['type'] = MENU_NORMAL_ITEM;
          // Insert item into the proper menu.
          $items[$path]['menu_name'] = $menu['name'];
          break;
        case 'tab':
          $items[$path]['type'] = MENU_CALLBACK;
          break;
        case 'default tab':
          $items[$path]['type'] = MENU_CALLBACK;
          break;
      }

      // Add context for contextual links.
      if (in_array($menu['type'], array('tab', 'default tab'))) {
        // @todo Remove once contextual links are ported to a new plugin based
        //   system.
        if (!empty($menu['context'])) {
          $items[$path]['context'] = TRUE;
        }
      }

      // If this is a 'default' tab, check to see if we have to create the
      // parent menu item.
      if ($this->isDefaultTabPath()) {
        $tab_options = $this->getOption('tab_options');

        $bits = explode('/', $path);
        // Remove the last piece.
        $bit = array_pop($bits);

        // Default tabs are handled by the local task plugins.
        if ($tab_options['type'] == 'tab') {
          return $items;
        }

        // we can't do this if they tried to make the last path bit variable.
        // @todo: We can validate this.
        if (!empty($bits)) {
          // Assign the route name to the parent route, not the default tab.
          $default_route_name = $items[$path]['route_name'];
          unset($items[$path]['route_name']);

          $default_path = implode('/', $bits);
          $items[$default_path] = array(
            // Default views page entry.
            // Identify URL embedded arguments and correlate them to a
            // handler.
            'load arguments'  => array($this->view->storage->id(), $this->display['id'], '%index'),
            'title' => $tab_options['title'],
            'description' => $tab_options['description'],
            'menu_name' => $tab_options['name'],
            'route_name' => $default_route_name,
          );
          switch ($tab_options['type']) {
            default:
            case 'normal':
              $items[$default_path]['type'] = MENU_NORMAL_ITEM;
              break;
          }
          if (isset($tab_options['weight'])) {
            $items[$default_path]['weight'] = intval($tab_options['weight']);
          }
        }
      }
    }

    return $items;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::execute().
   */
  public function execute() {
    // Prior to this being called, the $view should already be set to this
    // display, and arguments should be set on the view.
    $this->view->build();

    if (!empty($this->view->build_info['fail'])) {
      throw new NotFoundHttpException();
    }

    if (!empty($this->view->build_info['denied'])) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::optionsSummary().
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    $categories['page'] = array(
      'title' => t('Page settings'),
      'column' => 'second',
      'build' => array(
        '#weight' => -10,
      ),
    );

    $path = strip_tags($this->getOption('path'));

    if (empty($path)) {
      $path = t('No path is set');
    }
    else {
      $path = '/' . $path;
    }

    $options['path'] = array(
      'category' => 'page',
      'title' => t('Path'),
      'value' => views_ui_truncate($path, 24),
    );
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::buildOptionsForm().
   */
  public function buildOptionsForm(&$form, &$form_state) {
    parent::buildOptionsForm($form, $form_state);

    switch ($form_state['section']) {
      case 'path':
        $form['#title'] .= t('The menu path or URL of this view');
        $form['path'] = array(
          '#type' => 'textfield',
          '#title' => t('Path'),
          '#description' => t('This view will be displayed by visiting this path on your site. You may use "%" in your URL to represent values that will be used for contextual filters: For example, "node/%/feed". If needed you can even specify named route parameters like taxonomy/term/%taxonomy_term'),
          '#default_value' => $this->getOption('path'),
          '#field_prefix' => '<span dir="ltr">' . url(NULL, array('absolute' => TRUE)),
          '#field_suffix' => '</span>&lrm;',
          '#attributes' => array('dir' => 'ltr'),
        );
        break;
    }
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::validateOptionsForm().
   */
  public function validateOptionsForm(&$form, &$form_state) {
    parent::validateOptionsForm($form, $form_state);

    if ($form_state['section'] == 'path') {
      if (strpos($form_state['values']['path'], '%') === 0) {
        form_error($form['path'], $form_state, t('"%" may not be used for the first segment of a path.'));
      }

      // Automatically remove '/' and trailing whitespace from path.
      $form_state['values']['path'] = trim($form_state['values']['path'], '/ ');
    }
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::submitOptionsForm().
   */
  public function submitOptionsForm(&$form, &$form_state) {
    parent::submitOptionsForm($form, $form_state);

    if ($form_state['section'] == 'path') {
      $this->setOption('path', $form_state['values']['path']);
    }
  }

}
