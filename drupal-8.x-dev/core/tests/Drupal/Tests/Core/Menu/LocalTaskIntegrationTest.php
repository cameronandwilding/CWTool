<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Menu\LocalTaskUnitTest.
 */

namespace Drupal\Tests\Core\Menu;

use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

if (!defined('DRUPAL_ROOT')) {
  define('DRUPAL_ROOT', dirname(dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)))));
}

/**
 * Defines a base unit test for testing existence of local tasks.
 *
 * @todo Add tests for access checking and url building,
 *   https://drupal.org/node/2112245.
 */
abstract class LocalTaskIntegrationTest extends UnitTestCase {

  /**
   * A list of module directories used for YAML searching.
   *
   * @var array
   */
  protected $directoryList;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  protected function setUp() {
    $container = new ContainerBuilder();
    $config_factory = $this->getConfigFactoryStub(array());
    $container->set('config.factory', $config_factory);
    \Drupal::setContainer($container);
  }

  /**
   * Sets up the local task manager for the test.
   */
  protected function getLocalTaskManager($module_dirs, $route_name, $route_params) {
    $manager = $this
      ->getMockBuilder('Drupal\Core\Menu\LocalTaskManager')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $controllerResolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
    $property = new \ReflectionProperty('Drupal\Core\Menu\LocalTaskManager', 'controllerResolver');
    $property->setAccessible(TRUE);
    $property->setValue($manager, $controllerResolver);

    // todo mock a request with a route.
    $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
    $property = new \ReflectionProperty('Drupal\Core\Menu\LocalTaskManager', 'request');
    $property->setAccessible(TRUE);
    $property->setValue($manager, $request);

    $accessManager = $this->getMockBuilder('Drupal\Core\Access\AccessManager')
      ->disableOriginalConstructor()
      ->getMock();    $property = new \ReflectionProperty('Drupal\Core\Menu\LocalTaskManager', 'accessManager');
    $property->setAccessible(TRUE);
    $property->setValue($manager, $accessManager);

    $this->moduleHandler = $this->getMockBuilder('Drupal\Core\Extension\ModuleHandlerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $pluginDiscovery = new YamlDiscovery('local_tasks', $module_dirs);
    $pluginDiscovery = new ContainerDerivativeDiscoveryDecorator($pluginDiscovery);
    $property = new \ReflectionProperty('Drupal\Core\Menu\LocalTaskManager', 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($manager, $pluginDiscovery);

    $method = new \ReflectionMethod('Drupal\Core\Menu\LocalTaskManager', 'alterInfo');
    $method->setAccessible(TRUE);
    $method->invoke($manager, $this->moduleHandler, 'local_tasks');

    $plugin_stub = $this->getMock('Drupal\Core\Menu\LocalTaskInterface');
    $factory = $this->getMock('Drupal\Component\Plugin\Factory\FactoryInterface');
    $factory->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($plugin_stub));
    $property = new \ReflectionProperty('Drupal\Core\Menu\LocalTaskManager', 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($manager, $factory);

    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->returnValue(new Language(array('id' => 'en'))));

    $cache_backend = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $manager->setCacheBackend($cache_backend, $language_manager, 'local_task', array('local_task' => 1));

    return $manager;
  }

  /**
   * Tests integration for local tasks.
   *
   * @param $route_name
   *   Route name to base task building on.
   * @param $expected_tasks
   *   A list of tasks groups by level expected at the given route
   * @param array $route_params
   *   (optional) a list of route parameters used to resolve tasks.
   */
  protected function assertLocalTasks($route_name, $expected_tasks, $route_params = array()) {

    $directory_list = array();
    foreach ($this->directoryList as $key => $value) {
      $directory_list[$key] = DRUPAL_ROOT . '/' . $value;
    }

    $manager = $this->getLocalTaskManager($directory_list, $route_name, $route_params);

    $tmp_tasks = $manager->getLocalTasksForRoute($route_name);

    // At this point we're just testing existence so pull out keys and then
    // compare.
    //
    // Deeper testing would require a functioning factory which because we are
    // using the DefaultPluginManager base means we get into dependency soup
    // because its factories create method and pulling services off the \Drupal
    // container.
    $tasks = array();
    foreach ($tmp_tasks as $level => $level_tasks) {
      $tasks[$level] = array_keys($level_tasks);
    }
    $this->assertEquals($expected_tasks, $tasks);
  }

}
