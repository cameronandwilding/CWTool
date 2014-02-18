<?php

/**
 * @file
 * Contains Drupal\Core\Routing\UrlGenerator.
 */

namespace Drupal\Core\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Symfony\Cmf\Component\Routing\ProviderBasedGenerator;

use Drupal\Component\Utility\Settings;
use Drupal\Component\Utility\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;

/**
 * Generates URLs from route names and parameters.
 */
class UrlGenerator extends ProviderBasedGenerator implements UrlGeneratorInterface {

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The path processor to convert the system path to one suitable for urls.
   *
   * @var \Drupal\Core\PathProcessor\OutboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The route processor.
   *
   * @var \Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface
   */
  protected $routeProcessor;

  /**
   * The base path to use for urls.
   *
   * @var string
   */
  protected $basePath;

  /**
   * The base url to use for urls.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The script path to use for urls.
   *
   * @var string
   */
  protected $scriptPath;

  /**
   * Whether both secure and insecure session cookies can be used simultaneously.
   *
   * @var bool
   */
  protected $mixedModeSessions;

  /**
   *  Constructs a new generator object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $provider
   *   The route provider to be searched for routes.
   * @param \Drupal\Core\PathProcessor\OutboundPathProcessorInterface $path_processor
   *   The path processor to convert the system path to one suitable for urls.
   * @param \Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface $route_processor
   *   The route processor.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *    The config factory.
   * @param \Drupal\Component\Utility\Settings $settings
   *    The read only settings.
   * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
   *   An optional logger for recording errors.
   */
  public function __construct(RouteProviderInterface $provider, OutboundPathProcessorInterface $path_processor, OutboundRouteProcessorInterface $route_processor, ConfigFactoryInterface $config, Settings $settings, LoggerInterface $logger = NULL) {
    parent::__construct($provider, $logger);

    $this->pathProcessor = $path_processor;
    $this->routeProcessor = $route_processor;
    $this->mixedModeSessions = $settings->get('mixed_mode_sessions', FALSE);
    $allowed_protocols = $config->get('system.filter')->get('protocols') ?: array('http', 'https');
    Url::setAllowedProtocols($allowed_protocols);
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest(Request $request) {
    $this->request = $request;
    // Set some properties, based on the request, that are used during path-based
    // url generation.
    $this->basePath = $request->getBasePath() . '/';
    $this->baseUrl = $request->getSchemeAndHttpHost() . $this->basePath;
    $this->scriptPath = '';
    $base_path_with_script = $request->getBaseUrl();
    $script_name = $request->getScriptName();
    if (!empty($base_path_with_script) && strpos($base_path_with_script, $script_name) !== FALSE) {
      $length = strlen($this->basePath);
      $this->scriptPath = ltrim(substr($script_name, $length), '/') . '/';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPathFromRoute($name, $parameters = array()) {
    $route = $this->getRoute($name);
    $path = $this->getInternalPathFromRoute($route, $parameters);
    // Router-based paths may have a querystring on them but Drupal paths may
    // not have one, so remove any ? and anything after it. For generate() this
    // is handled in processPath().
    $path = preg_replace('/\?.*/', '', $path);
    return trim($path, '/');
  }

  /**
   * Gets the path of a route.
   *
   * @param \Symfony\Component\Routing\Route $route
   *  The route object.
   * @param array $parameters
   *  An array of parameters as passed to
   *  \Symfony\Component\Routing\Generator\UrlGeneratorInterface::generate().
   *
   * @return string
   *  The url path corresponding to the route, without the base path.
   */
  protected function getInternalPathFromRoute(SymfonyRoute $route, $parameters = array()) {
    // The Route has a cache of its own and is not recompiled as long as it does
    // not get modified.
    $compiledRoute = $route->compile();
    $hostTokens = $compiledRoute->getHostTokens();

    $route_requirements = $route->getRequirements();
    // We need to bypass the doGenerate() method's handling of absolute URLs as
    // we handle that ourselves after processing the path.
    if (isset($route_requirements['_scheme'])) {
      unset($route_requirements['_scheme']);
    }
    $path = $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route_requirements, $compiledRoute->getTokens(), $parameters, $route->getPath(), FALSE, $hostTokens);

    // The URL returned from doGenerate() will include the base path if there is
    // one (i.e., if running in a subdirectory) so we need to strip that off
    // before processing the path.
    $base_url = $this->context->getBaseUrl();
    if (!empty($base_url) && strpos($path, $base_url) === 0) {
      $path = substr($path, strlen($base_url));
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function generate($name, $parameters = array(), $absolute = FALSE) {
    $options['absolute'] = $absolute;
    return $this->generateFromRoute($name, $parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function generateFromRoute($name, $parameters = array(), $options = array()) {
    $absolute = !empty($options['absolute']);
    $route = $this->getRoute($name);
    $this->processRoute($route, $parameters);

    // Symfony adds any parameters that are not path slugs as query strings.
    if (isset($options['query']) && is_array($options['query'])) {
      $parameters = (array) $parameters + $options['query'];
    }

    $path = $this->getInternalPathFromRoute($route, $parameters);
    $path = $this->processPath($path, $options);
    $fragment = '';
    if (isset($options['fragment'])) {
      if (($fragment = trim($options['fragment'])) != '') {
        $fragment = '#' . $fragment;
      }
    }

    $base_url = $this->context->getBaseUrl();
    if (!$absolute || !$host = $this->context->getHost()) {
      return $base_url . $path . $fragment;
    }

    // Prepare an absolute URL by getting the correct scheme, host and port from
    // the request context.
    if (isset($options['https']) && $this->mixedModeSessions) {
      $scheme = $options['https'] ? 'https' : 'http';
    }
    else {
      $scheme = $this->context->getScheme();
    }
    $scheme_req = $route->getRequirement('_scheme');
    if (isset($scheme_req) && ($req = strtolower($scheme_req)) && $scheme !== $req) {
      $scheme = $req;
    }
    $port = '';
    if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
      $port = ':' . $this->context->getHttpPort();
    } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
      $port = ':' . $this->context->getHttpsPort();
    }
    return $scheme . '://' . $host . $port . $base_url . $path . $fragment;
  }

  /**
   * {@inheritdoc}
   */
  public function generateFromPath($path = NULL, $options = array()) {

    if (!$this->initialized()) {
      throw new GeneratorNotInitializedException();
    }

    // Merge in defaults.
    $options += array(
      'fragment' => '',
      'query' => array(),
      'absolute' => FALSE,
      'prefix' => '',
    );

    if (!isset($options['external'])) {
      // Return an external link if $path contains an allowed absolute URL. Only
      // call the slow \Drupal\Component\Utility\Url::stripDangerousProtocols()
      // if $path contains a ':' before any / ? or #. Note: we could use
      // url_is_external($path) here, but that would require another function
      // call, and performance inside url() is critical.
      $colonpos = strpos($path, ':');
      $options['external'] = ($colonpos !== FALSE && !preg_match('![/?#]!', substr($path, 0, $colonpos)) && Url::stripDangerousProtocols($path) == $path);
    }

    if (isset($options['fragment']) && $options['fragment'] !== '') {
      $options['fragment'] = '#' . $options['fragment'];
    }

    if ($options['external']) {
      // Split off the fragment.
      if (strpos($path, '#') !== FALSE) {
        list($path, $old_fragment) = explode('#', $path, 2);
        // If $options contains no fragment, take it over from the path.
        if (isset($old_fragment) && !$options['fragment']) {
          $options['fragment'] = '#' . $old_fragment;
        }
      }
      // Append the query.
      if ($options['query']) {
        $path .= (strpos($path, '?') !== FALSE ? '&' : '?') . Url::buildQuery($options['query']);
      }
      if (isset($options['https']) && $this->mixedModeSessions) {
        if ($options['https'] === TRUE) {
          $path = str_replace('http://', 'https://', $path);
        }
        elseif ($options['https'] === FALSE) {
          $path = str_replace('https://', 'http://', $path);
        }
      }
      // Reassemble.
      return $path . $options['fragment'];
    }
    else {
      $path = ltrim($this->processPath($path, $options), '/');
    }

    if (!isset($options['script'])) {
      $options['script'] = $this->scriptPath;
    }
    // The base_url might be rewritten from the language rewrite in domain mode.
    if (!isset($options['base_url'])) {
      if (isset($options['https']) && $this->mixedModeSessions) {
        if ($options['https'] === TRUE) {
          $options['base_url'] = str_replace('http://', 'https://', $this->baseUrl);
          $options['absolute'] = TRUE;
        }
        elseif ($options['https'] === FALSE) {
          $options['base_url'] = str_replace('https://', 'http://', $this->baseUrl);
          $options['absolute'] = TRUE;
        }
      }
      else {
        $options['base_url'] = $this->baseUrl;
      }
    }
    elseif (rtrim($options['base_url'], '/') == $options['base_url']) {
      $options['base_url'] .= '/';
    }
    $base = $options['absolute'] ? $options['base_url'] : $this->basePath;
    $prefix = empty($path) ? rtrim($options['prefix'], '/') : $options['prefix'];

    $path = str_replace('%2F', '/', rawurlencode($prefix . $path));
    $query = $options['query'] ? ('?' . Url::buildQuery($options['query'])) : '';
    return $base . $options['script'] . $path . $query . $options['fragment'];
  }

  /**
   * {@inheritdoc}
   */
  public function setBaseUrl($url) {
    $this->baseUrl = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function setBasePath($path) {
    $this->basePath = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function setScriptPath($path) {
    $this->scriptPath = $path;
  }

  /**
   * Passes the path to a processor manager to allow alterations.
   */
  protected function processPath($path, &$options = array()) {
    // Router-based paths may have a querystring on them.
    if ($query_pos = strpos($path, '?')) {
      // We don't need to do a strict check here because position 0 would mean we
      // have no actual path to work with.
      $actual_path = substr($path, 0, $query_pos);
      $query_string = substr($path, $query_pos);
    }
    else {
      $actual_path = $path;
      $query_string = '';
    }
    $path = '/' . $this->pathProcessor->processOutbound(trim($actual_path, '/'), $options, $this->request);
    $path .= $query_string;
    return $path;
  }

  /**
   * Passes the route to the processor manager for altering before complation.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route object to process.
   *
   * @param array $parameters
   *   An array of parameters to be passed to the route compiler.
   */
  protected function processRoute(SymfonyRoute $route, array &$parameters) {
    $this->routeProcessor->processOutbound($route, $parameters);
  }

  /**
   * Returns whether or not the url generator has been initialized.
   *
   * @return bool
   *   Returns TRUE if the basePath, baseUrl and scriptPath properties have been
   *   set, FALSE otherwise.
   */
  protected function initialized() {
    return isset($this->basePath) && isset($this->baseUrl) && isset($this->scriptPath);
  }

  /**
   * Find the route using the provided route name.
   *
   * @param string $name
   *   The route name to fetch
   *
   * @return \Symfony\Component\Routing\Route
   *   The found route.
   *
   * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
   *   Thrown if there is no route with that name in this repository.
   *
   * @see \Drupal\Core\Routing\RouteProviderInterface
   */
  protected function getRoute($name) {
    if ($name instanceof SymfonyRoute) {
      $route = $name;
    }
    elseif (NULL === $route = $this->provider->getRouteByName($name)) {
      throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
    }
    return $route;
  }

}
