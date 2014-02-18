<?php

/**
 * @file
 * Contains \Drupal\Core\Extension\ThemeHandler.
 */

namespace Drupal\Core\Extension;

use Drupal\Component\Utility\String;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Routing\RouteBuilder;
use Drupal\Core\SystemListingInfo;

/**
 * Default theme handler using the config system for enabled/disabled themes.
 */
class ThemeHandler implements ThemeHandlerInterface {

  /**
   * Contains the features enabled for themes by default.
   *
   * @var array
   */
  protected $defaultFeatures = array(
    'logo',
    'favicon',
    'name',
    'slogan',
    'node_user_picture',
    'comment_user_picture',
    'comment_user_verification',
    'main_menu',
    'secondary_menu',
  );

  /**
   * A list of all currently available themes.
   *
   * @var array
   */
  protected $list = array();

  /**
   * The config factory to get the enabled themes.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler to fire themes_enabled/themes_disabled hooks.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The cache backend to clear the local tasks cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   *  The config installer to install configuration.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * The info parser to parse the theme.info.yml files.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The route builder to rebuild the routes if a theme is enabled.
   *
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  protected $routeBuilder;

  /**
   * The system listing info
   *
   * @var \Drupal\Core\SystemListingInfo
   */
  protected $systemListingInfo;

  /**
   * Constructs a new ThemeHandler.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory to get the enabled themes.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to fire themes_enabled/themes_disabled hooks.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend to clear the local tasks cache.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser to parse the theme.info.yml files.
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   (optional) The config installer to install configuration. This optional
   *   to allow the theme handler to work before Drupal is installed and has a
   *   database.
   * @param \Drupal\Core\Routing\RouteBuilder $route_builder
   *   (optional) The route builder to rebuild the routes if a theme is enabled.
   * @param \Drupal\Core\SystemListingInfo $system_list_info
   *   (optional) The system listing info.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend, InfoParserInterface $info_parser, ConfigInstallerInterface $config_installer = NULL, RouteBuilder $route_builder = NULL, SystemListingInfo $system_list_info = NULL) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->cacheBackend = $cache_backend;
    $this->infoParser = $info_parser;
    $this->configInstaller = $config_installer;
    $this->routeBuilder = $route_builder;
    $this->systemListingInfo = $system_list_info;
  }

  /**
   * {@inheritdoc}
   */
  public function enable(array $theme_list) {
    $this->clearCssCache();
    $theme_config = $this->configFactory->get('system.theme');
    $disabled_themes = $this->configFactory->get('system.theme.disabled');
    foreach ($theme_list as $key) {
      // Throw an exception if the theme name is too long.
      if (strlen($key) > DRUPAL_EXTENSION_NAME_MAX_LENGTH) {
        throw new ExtensionNameLengthException(String::format('Theme name %name is over the maximum allowed length of @max characters.', array(
          '%name' => $key,
          '@max' => DRUPAL_EXTENSION_NAME_MAX_LENGTH,
        )));
      }

      // The value is not used; the weight is ignored for themes currently.
      $theme_config->set("enabled.$key", 0)->save();
      $disabled_themes->clear($key)->save();

      // Refresh the theme list as installation of default configuration needs
      // an updated list to work.
      $this->reset();
      // Install default configuration of the theme.
      $this->configInstaller->installDefaultConfig('theme', $key);
    }

    $this->resetSystem();

    // Invoke hook_themes_enabled() after the themes have been enabled.
    $this->moduleHandler->invokeAll('themes_enabled', array($theme_list));
  }

  /**
   * {@inheritdoc}
   */
  public function disable(array $theme_list) {
    // Don't disable the default theme.
    if ($pos = array_search($this->configFactory->get('system.theme')->get('default'), $theme_list) !== FALSE) {
      unset($theme_list[$pos]);
      if (empty($theme_list)) {
        return;
      }
    }

    $this->clearCssCache();

    $theme_config = $this->configFactory->get('system.theme');
    $disabled_themes = $this->configFactory->get('system.theme.disabled');
    foreach ($theme_list as $key) {
      // The value is not used; the weight is ignored for themes currently.
      $theme_config->clear("enabled.$key");
      $disabled_themes->set($key, 0);
    }
    $theme_config->save();
    $disabled_themes->save();

    $this->reset();
    $this->resetSystem();

    // Invoke hook_themes_disabled after the themes have been disabled.
    $this->moduleHandler->invokeAll('themes_disabled', array($theme_list));
  }

  /**
   * {@inheritdoc}
   */
  public function listInfo() {
    if (empty($this->list)) {
      $this->list = array();
      try {
        $themes = $this->systemThemeList();
      }
      catch (\Exception $e) {
        // If the database is not available, rebuild the theme data.
        $themes = $this->rebuildThemeData();
      }

      foreach ($themes as $theme) {
        foreach ($theme->info['stylesheets'] as $media => $stylesheets) {
          foreach ($stylesheets as $stylesheet => $path) {
            $theme->stylesheets[$media][$stylesheet] = $path;
          }
        }
        foreach ($theme->info['scripts'] as $script => $path) {
          $theme->scripts[$script] = $path;
        }
        if (isset($theme->info['engine'])) {
          $theme->engine = $theme->info['engine'];
        }
        if (isset($theme->info['base theme'])) {
          $theme->base_theme = $theme->info['base theme'];
        }
        // Status is normally retrieved from the database. Add zero values when
        // read from the installation directory to prevent notices.
        if (!isset($theme->status)) {
          $theme->status = 0;
        }
        $this->list[$theme->name] = $theme;
      }
    }
    return $this->list;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    // listInfo() calls system_info() which has a lot of side effects that have
    // to be triggered like the classloading of theme classes.
    $this->list = array();
    $this->systemListReset();
    $this->listInfo();
    $this->list = array();
  }

  /**
   * {@inheritdoc}
   */
  public function rebuildThemeData() {
    // Find themes.
    $listing = $this->getSystemListingInfo();
    $themes = $listing->scan('/^' . DRUPAL_PHP_FUNCTION_PATTERN . '\.info.yml$/', 'themes', 'name', 1);
    // Allow modules to add further themes.
    if ($module_themes = $this->moduleHandler->invokeAll('system_theme_info')) {
      foreach ($module_themes as $name => $uri) {
        // @see file_scan_directory()
        $themes[$name] = (object) array(
          'uri' => $uri,
          'filename' => pathinfo($uri, PATHINFO_FILENAME),
          'name' => $name,
        );
      }
    }

    // Find theme engines.
    $listing = $this->getSystemListingInfo();
    $engines = $listing->scan('/^' . DRUPAL_PHP_FUNCTION_PATTERN . '\.engine$/', 'themes/engines', 'name', 1);

    // Set defaults for theme info.
    $defaults = array(
      'engine' => 'twig',
      'regions' => array(
        'sidebar_first' => 'Left sidebar',
        'sidebar_second' => 'Right sidebar',
        'content' => 'Content',
        'header' => 'Header',
        'footer' => 'Footer',
        'highlighted' => 'Highlighted',
        'help' => 'Help',
        'page_top' => 'Page top',
        'page_bottom' => 'Page bottom',
      ),
      'description' => '',
      'features' => $this->defaultFeatures,
      'screenshot' => 'screenshot.png',
      'php' => DRUPAL_MINIMUM_PHP,
      'stylesheets' => array(),
      'scripts' => array(),
    );

    $sub_themes = array();
    // Read info files for each theme.
    foreach ($themes as $key => $theme) {
      $themes[$key]->filename = $theme->uri;
      $themes[$key]->info = $this->infoParser->parse($theme->uri) + $defaults;

      // Skip this extension if its type is not theme.
      if (!isset($themes[$key]->info['type']) || $themes[$key]->info['type'] != 'theme') {
        unset($themes[$key]);
        continue;
      }

      // Add the info file modification time, so it becomes available for
      // contributed modules to use for ordering theme lists.
      $themes[$key]->info['mtime'] = filemtime($theme->uri);

      // Invoke hook_system_info_alter() to give installed modules a chance to
      // modify the data in the .info.yml files if necessary.
      $type = 'theme';
      $this->moduleHandler->alter('system_info', $themes[$key]->info, $themes[$key], $type);

      if (!empty($themes[$key]->info['base theme'])) {
        $sub_themes[] = $key;
      }

      $engine = $themes[$key]->info['engine'];
      if (isset($engines[$engine])) {
        $themes[$key]->owner = $engines[$engine]->uri;
        $themes[$key]->prefix = $engines[$engine]->name;
        $themes[$key]->template = TRUE;
      }

      // Prefix stylesheets and scripts with module path.
      $path = dirname($theme->uri);
      $theme->info['stylesheets'] = $this->themeInfoPrefixPath($theme->info['stylesheets'], $path);
      $theme->info['scripts'] = $this->themeInfoPrefixPath($theme->info['scripts'], $path);

      // Give the screenshot proper path information.
      if (!empty($themes[$key]->info['screenshot'])) {
        $themes[$key]->info['screenshot'] = $path . '/' . $themes[$key]->info['screenshot'];
      }
    }

    // Now that we've established all our master themes, go back and fill in
    // data for sub-themes.
    foreach ($sub_themes as $key) {
      $themes[$key]->base_themes = $this->doGetBaseThemes($themes, $key);
      // Don't proceed if there was a problem with the root base theme.
      if (!current($themes[$key]->base_themes)) {
        continue;
      }
      $base_key = key($themes[$key]->base_themes);
      foreach (array_keys($themes[$key]->base_themes) as $base_theme) {
        $themes[$base_theme]->sub_themes[$key] = $themes[$key]->info['name'];
      }
      // Copy the 'owner' and 'engine' over if the top level theme uses a theme
      // engine.
      if (isset($themes[$base_key]->owner)) {
        if (isset($themes[$base_key]->info['engine'])) {
          $themes[$key]->info['engine'] = $themes[$base_key]->info['engine'];
          $themes[$key]->owner = $themes[$base_key]->owner;
          $themes[$key]->prefix = $themes[$base_key]->prefix;
        }
        else {
          $themes[$key]->prefix = $key;
        }
      }
    }

    return $themes;
  }

  /**
   * Prefixes all values in an .info.yml file array with a given path.
   *
   * This helper function is mainly used to prefix all array values of an
   * .info.yml file property with a single given path (to the module or theme);
   * e.g., to prefix all values of the 'stylesheets' or 'scripts' properties
   * with the file path to the defining module/theme.
   *
   * @param array $info
   *   A nested array of data of an .info.yml file to be processed.
   * @param string $path
   *   A file path to prepend to each value in $info.
   *
   * @return array
   *   The $info array with prefixed values.
   *
   * @see _system_rebuild_module_data()
   * @see self::rebuildThemeData()
   */
  protected function themeInfoPrefixPath(array $info, $path) {
    foreach ($info as $key => $value) {
      // Recurse into nested values until we reach the deepest level.
      if (is_array($value)) {
        $info[$key] = $this->themeInfoPrefixPath($info[$key], $path);
      }
      // Unset the original value's key and set the new value with prefix, using
      // the original value as key, so original values can still be looked up.
      else {
        unset($info[$key]);
        $info[$value] = $path . '/' . $value;
      }
    }
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseThemes(array $themes, $theme) {
    return $this->doGetBaseThemes($themes, $theme);
  }

  /**
   * Finds the base themes for the specific theme.
   *
   * @param array $themes
   *   An array of available themes.
   * @param string $theme
   *   The name of the theme whose base we are looking for.
   * @param array $used_themes
   *   (optional) A recursion parameter preventing endless loops. Defaults to
   *   an empty array.
   *
   * @return array
   *   An array of base themes.
   */
  protected function doGetBaseThemes(array $themes, $theme, $used_themes = array()) {
    if (!isset($themes[$theme]->info['base theme'])) {
      return array();
    }

    $base_key = $themes[$theme]->info['base theme'];
    // Does the base theme exist?
    if (!isset($themes[$base_key])) {
      return array($base_key => NULL);
    }

    $current_base_theme = array($base_key => $themes[$base_key]->info['name']);

    // Is the base theme itself a child of another theme?
    if (isset($themes[$base_key]->info['base theme'])) {
      // Do we already know the base themes of this theme?
      if (isset($themes[$base_key]->base_themes)) {
        return $themes[$base_key]->base_themes + $current_base_theme;
      }
      // Prevent loops.
      if (!empty($used_themes[$base_key])) {
        return array($base_key => NULL);
      }
      $used_themes[$base_key] = TRUE;
      return $this->getBaseThemes($themes, $base_key, $used_themes) + $current_base_theme;
    }
    // If we get here, then this is our parent theme.
    return $current_base_theme;
  }

  /**
   * Returns a system listing info object.
   *
   * @return \Drupal\Core\SystemListingInfo
   *   The system listing object.
   */
  protected function getSystemListingInfo() {
    if (!isset($this->systemListingInfo)) {
      $this->systemListingInfo = new SystemListingInfo();
    }
    return $this->systemListingInfo;
  }

  /**
   * Resets some other systems like rebuilding the route information or caches.
   */
  protected function resetSystem() {
    if ($this->routeBuilder) {
      $this->routeBuilder->setRebuildNeeded();
    }
    $this->systemListReset();

    // @todo It feels wrong to have the requirement to clear the local tasks
    //   cache here.
    Cache::deleteTags(array('local_task' => 1));
    $this->themeRegistryRebuild();
  }

  /**
   * Wraps system_list_reset().
   */
  protected function systemListReset() {
    system_list_reset();
  }

  /**
   * Wraps drupal_clear_css_cache().
   */
  protected function clearCssCache() {
    drupal_clear_css_cache();
  }

  /**
   * Wraps drupal_theme_rebuild().
   */
  protected function themeRegistryRebuild() {
    drupal_theme_rebuild();
  }

  /**
   * Wraps system_list().
   *
   * @return array
   *   A list of themes keyed by name.
   */
  protected function systemThemeList() {
    return system_list('theme');
  }

}
