<?php

/**
 * @file
 * Definition of Drupal\block\Tests\BlockRenderOrderTest.
 */

namespace Drupal\block\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests block HTML ID validity.
 */
class BlockRenderOrderTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'block');

  public static function getInfo() {
    return array(
      'name' => 'Block Render Order',
      'description' => 'Test blocks are being rendered in order by weight.',
      'group' => 'Block',
    );
  }

  function setUp() {
    parent::setUp();
    // Create a test user.
    $end_user = $this->drupalCreateUser(array(
      'access content',
    ));
    $this->drupalLogin($end_user);
  }

  /**
   * Tests the render order of the blocks.
   */
  function testBlockRenderOrder() {
    // Enable test blocks and place them in the same region.
    $region = 'header';
    $test_blocks = array(
      'stark_powered' => array(
        'weight' => '-3',
        'id' => 'stark_powered',
      ),
      'stark_by' => array(
        'weight' => '3',
        'id' => 'stark_by',
      ),
      'stark_drupal' => array(
        'weight' => '3',
        'id' => 'stark_drupal',
      ),
    );

    // Place the test blocks.
    foreach ($test_blocks as $test_block) {
      $this->drupalPlaceBlock('system_powered_by_block', array(
        'label' => 'Test Block',
        'region' => $region,
        'weight' => $test_block['weight'],
        'id' => $test_block['id'],
      ));
    }

    $this->drupalGet('');
    $test_content = $this->drupalGetContent('');

    $controller = $this->container->get('entity.manager')->getStorageController('block');
    foreach ($controller->loadMultiple() as $return_block) {
      $id = $return_block->get('id');
      if ($return_block_weight = $return_block->get('weight')) {
        $this->assertTrue($test_blocks[$id]['weight'] == $return_block_weight, 'Block weight is set as "' . $return_block_weight  . '" for ' . $id . ' block.');
        $position[$id] = strpos($test_content, drupal_html_class('block-' . $test_blocks[$id]['id']));
      }
    }
    $this->assertTrue($position['stark_powered'] < $position['stark_by'], 'Blocks with different weight are rendered in the correct order.');
    $this->assertTrue($position['stark_drupal'] < $position['stark_by'], 'Blocks with identical weight are rendered in reverse alphabetical order.');
  }
}
