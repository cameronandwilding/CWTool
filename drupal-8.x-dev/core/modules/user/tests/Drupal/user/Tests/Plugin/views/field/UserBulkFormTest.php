<?php

/**
 * @file
 * Contains \Drupal\user\Tests\Plugin\views\field\UserBulkFormTest.
 */

namespace Drupal\user\Tests\Plugin\views\field;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Plugin\views\field\UserBulkForm;

/**
 * Tests the user bulk form plugin.
 *
 * @see \Drupal\user\Plugin\views\field\UserBulkForm
 */
class UserBulkFormTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'User: Bulk form',
      'description' => 'Tests the user bulk form plugin.',
      'group' => 'Views module integration',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    parent::tearDown();
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
  }

  /**
   * Tests the constructor assignment of actions.
   */
  public function testConstructor() {
    $actions = array();

    for ($i = 1; $i <= 2; $i++) {
      $action = $this->getMock('\Drupal\system\ActionConfigEntityInterface');
      $action->expects($this->any())
        ->method('getType')
        ->will($this->returnValue('user'));
      $actions[$i] = $action;
    }

    $action = $this->getMock('\Drupal\system\ActionConfigEntityInterface');
    $action->expects($this->any())
      ->method('getType')
      ->will($this->returnValue('node'));
    $actions[] = $action;

    $storage_controller = $this->getMock('Drupal\Core\Entity\EntityStorageControllerInterface');
    $storage_controller->expects($this->any())
      ->method('loadMultiple')
      ->will($this->returnValue($actions));

    $views_data = $this->getMockBuilder('Drupal\views\ViewsData')
      ->disableOriginalConstructor()
      ->getMock();
    $views_data->expects($this->any())
      ->method('get')
      ->with('users')
      ->will($this->returnValue(array('table' => array('entity type' => 'user'))));
    $container = new ContainerBuilder();
    $container->set('views.views_data', $views_data);
    \Drupal::setContainer($container);

    $storage = $this->getMock('Drupal\views\ViewStorageInterface');
    $storage->expects($this->any())
      ->method('get')
      ->with('base_table')
      ->will($this->returnValue('users'));

    $executable = $this->getMockBuilder('Drupal\views\ViewExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $executable->storage = $storage;

    $display = $this->getMockBuilder('Drupal\views\Plugin\views\display\DisplayPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $definition['title'] = '';
    $options = array();

    $user_bulk_form = new UserBulkForm(array(), 'user_bulk_form', $definition, $storage_controller);
    $user_bulk_form->init($executable, $display, $options);

    $this->assertAttributeEquals(array_slice($actions, 0, -1, TRUE), 'actions', $user_bulk_form);
  }

}
