<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\ExternalUrlTest.
 */

namespace Drupal\Tests\Core;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Tests the \Drupal\Core\Url class for external paths.
 *
 * @group Drupal
 * @group Url
 *
 * @coversDefaultClass \Drupal\Core\Url
 */
class ExternalUrlTest extends UnitTestCase {

  /**
   * The URL generator
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * The router.
   *
   * @var \Drupal\Tests\Core\Routing\TestRouterInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $router;

  /**
   * An external URL to test.
   *
   * @var string
   */
  protected $path = 'http://drupal.org';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Url object (external)',
      'description' => 'Tests the \Drupal\Core\Url class with external paths.',
      'group' => 'Routing',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->urlGenerator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $this->urlGenerator->expects($this->any())
      ->method('generateFromPath')
      ->will($this->returnCallback(function ($path) {
        return $path;
      }));

    $this->router = $this->getMock('Drupal\Tests\Core\Routing\TestRouterInterface');
    $container = new ContainerBuilder();
    $container->set('router', $this->router);
    $container->set('url_generator', $this->urlGenerator);
    \Drupal::setContainer($container);
  }

  /**
   * Tests the createFromPath method.
   *
   * @covers ::createFromPath()
   * @covers ::setExternal()
   */
  public function testCreateFromPath() {
    $url = Url::createFromPath($this->path);
    $this->assertInstanceOf('Drupal\Core\Url', $url);
    $this->assertTrue($url->isExternal());
    return $url;
  }

  /**
   * Tests the createFromRequest method.
   *
   * @covers ::createFromRequest()
   *
   * @expectedException \Drupal\Core\Routing\MatchingRouteNotFoundException
   * @expectedExceptionMessage No matching route could be found for the request: request_as_a_string
   */
  public function testCreateFromRequest() {
    // Mock the request in order to override the __toString() method.
    $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('request_as_a_string'));

    $this->router->expects($this->once())
      ->method('matchRequest')
      ->with($request)
      ->will($this->throwException(new ResourceNotFoundException()));

    $this->assertNull(Url::createFromRequest($request));
  }

  /**
   * Tests the isExternal() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::isExternal()
   */
  public function testIsExternal(Url $url) {
    $this->assertTrue($url->isExternal());
  }

  /**
   * Tests the toString() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::toString()
   */
  public function testToString(Url $url) {
    $this->assertSame($this->path, $url->toString());
  }

  /**
   * Tests the toArray() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::toArray()
   */
  public function testToArray(Url $url) {
    $expected = array(
      'route_name' => '',
      'route_parameters' => array(),
      'options' => array(),
    );
    $this->assertSame($expected, $url->toArray());
  }

  /**
   * Tests the getRouteName() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::getRouteName()
   */
  public function testGetRouteName(Url $url) {
    $this->assertSame('', $url->getRouteName());
  }

  /**
   * Tests the getRouteParameters() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::getRouteParameters()
   */
  public function testGetRouteParameters(Url $url) {
    $this->assertSame(array(), $url->getRouteParameters());
  }

  /**
   * Tests the getInternalPath() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::getInternalPath()
   *
   * @expectedException \Exception
   */
  public function testGetInternalPath(Url $url) {
    $this->assertNull($url->getInternalPath());
  }

  /**
   * Tests the getOptions() method.
   *
   * @depends testCreateFromPath
   *
   * @covers ::getOptions()
   */
  public function testGetOptions(Url $url) {
    $this->assertInternalType('array', $url->getOptions());
  }

}
