<?php

/**
 * @file
 * Contains \Drupal\views\Tests\Plugin\DisplayPageTest.
 */

namespace Drupal\views\Tests\Plugin;

use Drupal\views\Tests\ViewUnitTestBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests the page display plugin.
 *
 * @see \Drupal\views\Plugin\display\Page
 */
class DisplayPageTest extends ViewUnitTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_page_display', 'test_page_display_route');

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'user', 'menu_link', 'field', 'entity');

  /**
   * The router dumper to get all routes.
   *
   * @var \Drupal\Core\Routing\MatcherDumper
   */
  protected $routerDumper;

  public static function getInfo() {
    return array(
      'name' => 'Display: Page plugin',
      'description' => 'Tests the page display plugin.',
      'group' => 'Views Plugins',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup the needed tables in order to make the drupal router working.
    $this->installSchema('system', array('menu_router', 'url_alias'));
    $this->installSchema('menu_link', 'menu_links');
  }

  /**
   * Checks the behavior of the page for access denied/not found behaviors.
   */
  public function testPageResponses() {
    $subrequest = Request::create('/test_page_display_403', 'GET');
    $response = $this->container->get('http_kernel')->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
    $this->assertEqual($response->getStatusCode(), 403);

    $subrequest = Request::create('/test_page_display_404', 'GET');
    $response = $this->container->get('http_kernel')->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
    $this->assertEqual($response->getStatusCode(), 404);

    $subrequest = Request::create('/test_page_display_200', 'GET');
    $response = $this->container->get('http_kernel')->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
    $this->assertEqual($response->getStatusCode(), 200);

    $subrequest = Request::create('/test_page_display_200', 'GET');
    \Drupal::getContainer()->set('request', $subrequest);

    // Test accessing a disabled page for a view.
    $view = views_get_view('test_page_display');
    // Disable the view, rebuild menu, and request the page again.
    $view->storage->disable()->save();
    // Router rebuild would occur in a kernel terminate event so we need to
    // simulate that here.
    \Drupal::service('router.builder')->rebuildIfNeeded();

    $response = $this->container->get('http_kernel')->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
    $this->assertEqual($response->getStatusCode(), 404);
  }

  /**
   * Checks that the router items are properly registered
   */
  public function testPageRouterItems() {
    $collection = \Drupal::service('views.route_subscriber')->routes();

    // Check the controller defaults.
    foreach ($collection as $id => $route) {
      if (strpos($id, 'test_page_display_route') === 0) {
        $this->assertEqual($route->getDefault('_controller'), 'Drupal\views\Routing\ViewPageController::handle');
        $this->assertEqual($route->getDefault('view_id'), 'test_page_display_route');
        $this->assertEqual($route->getDefault('display_id'), str_replace('test_page_display_route.', '', $id));
      }
    }

    // Check the generated patterns and default values.
    $route = $collection->get('view.test_page_display_route.page_1');
    $this->assertEqual($route->getPath(), '/test_route_without_arguments');

    $route = $collection->get('view.test_page_display_route.page_2');
    $this->assertEqual($route->getPath(), '/test_route_with_argument/{arg_0}');
    $this->assertTrue($route->hasDefault('arg_0'), 'A default value is set for the optional argument id.');

    $route = $collection->get('view.test_page_display_route.page_3');
    $this->assertEqual($route->getPath(), '/test_route_with_argument/{arg_0}/suffix');
    $this->assertFalse($route->hasDefault('arg_0'), 'No default value is set for the required argument id.');

    $route = $collection->get('view.test_page_display_route.page_4');
    $this->assertEqual($route->getPath(), '/test_route_with_argument/{arg_0}/suffix/{arg_1}');
    $this->assertFalse($route->hasDefault('arg_0'), 'No default value is set for the required argument id.');
    $this->assertTrue($route->hasDefault('arg_1'), 'A default value is set for the optional argument id_2.');

    $route = $collection->get('view.test_page_display_route.page_5');
    $this->assertEqual($route->getPath(), '/test_route_with_argument/{arg_0}/{arg_1}');
    $this->assertTrue($route->hasDefault('arg_0'), 'A default value is set for the optional argument id.');
    $this->assertTrue($route->hasDefault('arg_1'), 'A default value is set for the optional argument id_2.');

    $route = $collection->get('view.test_page_display_route.page_6');
    $this->assertEqual($route->getPath(), '/test_route_with_argument/{arg_0}/{arg_1}');
    $this->assertFalse($route->hasDefault('arg_0'), 'No default value is set for the required argument id.');
    $this->assertFalse($route->hasDefault('arg_1'), 'No default value is set for the required argument id_2.');
  }

}
