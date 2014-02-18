<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Access\CustomAccessCheckTest.
 */

namespace Drupal\Tests\Core\Access;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Access\CustomAccessCheck;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Tests the custom access checker.
 *
 * @see \Drupal\Core\Access\CustomAccessCheck
 */
class CustomAccessCheckTest extends UnitTestCase {

  /**
   * The access checker to test.
   *
   * @var \Drupal\Core\Access\CustomAccessCheck
   */
  protected $accessChecker;

  /**
   * The mocked controller resolver.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $controllerResolver;

  public static function getInfo() {
    return array(
      'name' => 'Custom access check',
      'description' => 'Tests the custom access checker.',
      'group' => 'Access'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->controllerResolver = $this->getMock('Drupal\Core\Controller\ControllerResolverInterface');
    $this->accessChecker = new CustomAccessCheck($this->controllerResolver);
  }

  /**
   * Test the access method.
   */
  public function testAccess() {
    $request = new Request(array());

    $this->controllerResolver->expects($this->at(0))
      ->method('getControllerFromDefinition')
      ->with('\Drupal\Tests\Core\Access\TestController::accessDeny')
      ->will($this->returnValue(array(new TestController(), 'accessDeny')));

    $this->controllerResolver->expects($this->at(1))
      ->method('getArguments')
      ->will($this->returnValue(array()));

    $this->controllerResolver->expects($this->at(2))
      ->method('getControllerFromDefinition')
      ->with('\Drupal\Tests\Core\Access\TestController::accessAllow')
      ->will($this->returnValue(array(new TestController(), 'accessAllow')));

    $this->controllerResolver->expects($this->at(3))
      ->method('getArguments')
      ->will($this->returnValue(array()));

    $this->controllerResolver->expects($this->at(4))
      ->method('getControllerFromDefinition')
      ->with('\Drupal\Tests\Core\Access\TestController::accessParameter')
      ->will($this->returnValue(array(new TestController(), 'accessParameter')));

    $this->controllerResolver->expects($this->at(5))
      ->method('getArguments')
      ->will($this->returnValue(array('parameter' => 'TRUE')));

    $route = new Route('/test-route', array(), array('_custom_access' => '\Drupal\Tests\Core\Access\TestController::accessDeny'));
    $account = $this->getMock('Drupal\Core\Session\AccountInterface');
    $this->assertSame(AccessInterface::DENY, $this->accessChecker->access($route, $request, $account));

    $route = new Route('/test-route', array(), array('_custom_access' => '\Drupal\Tests\Core\Access\TestController::accessAllow'));
    $this->assertSame(AccessInterface::ALLOW, $this->accessChecker->access($route, $request, $account));

    $route = new Route('/test-route', array('parameter' => 'TRUE'), array('_custom_access' => '\Drupal\Tests\Core\Access\TestController::accessParameter'));
    $this->assertSame(AccessInterface::ALLOW, $this->accessChecker->access($route, $request, $account));
  }

}

class TestController {

  public function accessAllow() {
    return AccessInterface::ALLOW;
  }

  public function accessDeny() {
    return AccessInterface::DENY;
  }

  public function accessParameter($parameter) {
    if ($parameter == 'TRUE') {
      return AccessInterface::ALLOW;
    }
    else {
      return AccessInterface::DENY;
    }
  }

}
