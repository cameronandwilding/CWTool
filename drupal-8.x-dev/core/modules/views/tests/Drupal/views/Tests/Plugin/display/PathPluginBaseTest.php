<?php

/**
 * @file
 * Contains \Drupal\views\Tests\Plugin\display\PathPluginBaseTest.
 */

namespace Drupal\views\Tests\Plugin\display {

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests the abstract base class for path based display plugins.
 *
 * @see \Drupal\views\Plugin\views\display\PathPluginBase
 */
class PathPluginBaseTest extends UnitTestCase {

  /**
   * The route provider that should be used.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $routeProvider;

  /**
   * The tested path plugin base.
   *
   * @var \Drupal\views\Plugin\views\display\PathPluginBase
   */
  protected $pathPlugin;

  /**
   * The mocked views access plugin manager.
   *
   * @var \Drupal\views\Plugin\ViewsPluginManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $accessPluginManager;

  /**
   * The mocked key value storage.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $state;

  public static function getInfo() {
    return array(
      'name' => 'Display: Path plugin base.',
      'description' => 'Tests the abstract base class for path based display plugins.',
      'group' => 'Views Plugins',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->routeProvider = $this->getMock('Drupal\Core\Routing\RouteProviderInterface');
    $this->state = $this->getMock('\Drupal\Core\KeyValueStore\StateInterface');
    $this->pathPlugin = $this->getMockBuilder('Drupal\views\Plugin\views\display\PathPluginBase')
      ->setConstructorArgs(array(array(), 'path_base', array(), $this->routeProvider, $this->state))
      ->setMethods(NULL)
      ->getMock();
    $this->setupContainer();
  }

  /**
   * Setup access plugin manager and config factory in the Drupal class.
   */
  public function setupContainer() {
    $this->accessPluginManager = $this->getMockBuilder('\Drupal\views\Plugin\ViewsPluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $container = new ContainerBuilder();
    $container->set('plugin.manager.views.access', $this->accessPluginManager);

    $config = array(
      'views.settings' => array(
        'skip_cache' => TRUE,
        'display_extenders' => array(),
      ),
    );

    $container->set('config.factory', $this->getConfigFactoryStub($config));

    \Drupal::setContainer($container);
  }

  /**
   * Tests the collectRoutes method.
   *
   * @see \Drupal\views\Plugin\views\display\PathPluginBase::collectRoutes()
   */
  public function testCollectRoutes() {
    list($view) = $this->setupViewExecutableAccessPlugin();

    $display = array();
    $display['display_plugin'] = 'page';
    $display['id'] = 'page_1';
    $display['display_options'] = array(
      'path' => 'test_route',
    );
    $this->pathPlugin->initDisplay($view, $display);

    $collection = new RouteCollection();
    $result = $this->pathPlugin->collectRoutes($collection);
    $this->assertEquals(array('test_id.page_1' => 'view.test_id.page_1'), $result);

    $route = $collection->get('view.test_id.page_1');
    $this->assertTrue($route instanceof Route);
    $this->assertEquals('test_id', $route->getDefault('view_id'));
    $this->assertEquals('page_1', $route->getDefault('display_id'));
  }

  /**
   * Tests the collect routes method with an alternative route name in the UI.
   */
  public function testCollectRoutesWithSpecialRouteName() {
    list($view) = $this->setupViewExecutableAccessPlugin();

    $display = array();
    $display['display_plugin'] = 'page';
    $display['id'] = 'page_1';
    $display['display_options'] = array(
      'path' => 'test_route',
      'route_name' => 'test_route',
    );
    $this->pathPlugin->initDisplay($view, $display);

    $collection = new RouteCollection();
    $result = $this->pathPlugin->collectRoutes($collection);
    $this->assertEquals(array('test_id.page_1' => 'test_route'), $result);

    $route = $collection->get('test_route');
    $this->assertTrue($route instanceof Route);
    $this->assertEquals('test_id', $route->getDefault('view_id'));
    $this->assertEquals('page_1', $route->getDefault('display_id'));
  }

  /**
   * Tests the alter route method.
   */
  public function testAlterRoute() {
    $collection = new RouteCollection();
    $collection->add('test_route', new Route('test_route', array('_controller' => 'Drupal\Tests\Core\Controller\TestController::content')));
    $route_2 = new Route('test_route/example', array('_controller' => 'Drupal\Tests\Core\Controller\TestController::content'));
    $collection->add('test_route_2', $route_2);

    list($view) = $this->setupViewExecutableAccessPlugin();

    $display = array();
    $display['display_plugin'] = 'page';
    $display['id'] = 'page_1';
    $display['display_options'] = array(
      'path' => 'test_route',
    );
    $this->pathPlugin->initDisplay($view, $display);

    $view_route_names = $this->pathPlugin->alterRoutes($collection);
    $this->assertEquals(array('test_id.page_1' => 'test_route'), $view_route_names);

    // Ensure that the test_route is overridden.
    $route = $collection->get('test_route');
    $this->assertTrue($route instanceof Route);
    $this->assertEquals('test_id', $route->getDefault('view_id'));
    $this->assertEquals('page_1', $route->getDefault('display_id'));

    // Ensure that the test_route_2 is not overridden.
    $route = $collection->get('test_route_2');
    $this->assertTrue($route instanceof Route);
    $this->assertFalse($route->hasDefault('view_id'));
    $this->assertFalse($route->hasDefault('display_id'));
    $this->assertSame($collection->get('test_route_2'), $route_2);
  }

  /**
   * Tests the collectRoutes method with a path containing named parameters.
   *
   * @see \Drupal\views\Plugin\views\display\PathPluginBase::collectRoutes()
   */
  public function testCollectRoutesWithNamedParameters() {
    /** @var \Drupal\views\ViewExecutable|\PHPUnit_Framework_MockObject_MockObject $view */
    list($view) = $this->setupViewExecutableAccessPlugin();

    $view->expects($this->once())
      ->method('initHandlers');
    $view->argument = array();
    $view->argument['nid'] = $this->getMockBuilder('Drupal\views\Plugin\views\argument\ArgumentPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $display = array();
    $display['display_plugin'] = 'page';
    $display['id'] = 'page_1';
    $display['display_options'] = array(
      'path' => 'test_route/%node/example',
    );
    $this->pathPlugin->initDisplay($view, $display);

    $collection = new RouteCollection();
    $result = $this->pathPlugin->collectRoutes($collection);
    $this->assertEquals(array('test_id.page_1' => 'view.test_id.page_1'), $result);

    $route = $collection->get('view.test_id.page_1');
    $this->assertTrue($route instanceof Route);
    $this->assertEquals('/test_route/{node}/example', $route->getPath());
    $this->assertEquals('test_id', $route->getDefault('view_id'));
    $this->assertEquals('page_1', $route->getDefault('display_id'));
    $this->assertEquals(array('arg_0' => 'node'), $route->getDefault('_view_argument_map'));
  }

  /**
   * Tests alter routes with parameters in the overriding route.
   */
  public function testAlterRoutesWithParameters() {
    $collection = new RouteCollection();
    $collection->add('test_route', new Route('test_route/{parameter}', array('_controller' => 'Drupal\Tests\Core\Controller\TestController::content')));

    list($view) = $this->setupViewExecutableAccessPlugin();

    // Manually setup an argument handler.
    $argument = $this->getMockBuilder('Drupal\views\Plugin\views\argument\ArgumentPluginBase')
      ->disableOriginalConstructor()
      ->getMock();
    $view->argument['test_id'] = $argument;

    $display = array();
    $display['display_plugin'] = 'page';
    $display['id'] = 'page_1';
    $display['display_options'] = array(
      'path' => 'test_route/%',
    );
    $this->pathPlugin->initDisplay($view, $display);

    $view_route_names = $this->pathPlugin->alterRoutes($collection);
    $this->assertEquals(array('test_id.page_1' => 'test_route'), $view_route_names);

    // Ensure that the test_route is overridden.
    $route = $collection->get('test_route');
    $this->assertInstanceOf('\Symfony\Component\Routing\Route', $route);
    $this->assertEquals('test_id', $route->getDefault('view_id'));
    $this->assertEquals('page_1', $route->getDefault('display_id'));
    // Ensure that the path did not changed and placeholders are respected.
    $this->assertEquals('/test_route/{parameter}', $route->getPath());
    $this->assertEquals(array('arg_0' => 'parameter'), $route->getDefault('_view_argument_map'));
  }

  /**
   * Returns some mocked view entity, view executable, and access plugin.
   */
  protected function setupViewExecutableAccessPlugin() {
    $view_entity = $this->getMockBuilder('Drupal\views\Entity\View')
      ->disableOriginalConstructor()
      ->getMock();
    $view_entity->expects($this->any())
      ->method('id')
      ->will($this->returnValue('test_id'));

    $view = $this->getMockBuilder('Drupal\views\ViewExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $view->storage = $view_entity;

    // Skip views options caching.
    $view->editing = TRUE;

    $access_plugin = $this->getMockBuilder('Drupal\views\Plugin\views\access\AccessPluginBase')
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->accessPluginManager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($access_plugin));

    return array($view, $view_entity, $access_plugin);
  }

}

}

namespace {
  if (!function_exists('views_get_enabled_display_extenders')) {
    function views_get_enabled_display_extenders() {
      return array();
    }
  }
}
