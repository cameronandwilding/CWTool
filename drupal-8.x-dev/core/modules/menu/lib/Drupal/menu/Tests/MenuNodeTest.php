<?php

/**
 * @file
 * Definition of Drupal\menu\Tests\MenuNodeTest.
 */

namespace Drupal\menu\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test menu settings for nodes.
 */
class MenuNodeTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('menu', 'test_page_test', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Menu settings for nodes',
      'description' => 'Add, edit, and delete a node with menu link.',
      'group' => 'Menu',
    );
  }

  function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    $this->admin_user = $this->drupalCreateUser(array(
      'access administration pages',
      'administer content types',
      'administer menu',
      'create page content',
      'edit any page content',
      'delete any page content',
    ));
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Test creating, editing, deleting menu links via node form widget.
   */
  function testMenuNodeFormWidget() {
    // Disable the default main menu, so that no menus are enabled.
    $edit = array(
      'menu_options[main]' => FALSE,
    );
    $this->drupalPostForm('admin/structure/types/manage/page', $edit, t('Save content type'));

    // Verify that no menu settings are displayed and nodes can be created.
    $this->drupalGet('node/add/page');
    $this->assertText(t('Create Basic page'));
    $this->assertNoText(t('Menu settings'));
    $node_title = $this->randomName();
    $edit = array(
      'title[0][value]' => $node_title,
      'body[0][value]' => $this->randomString(),
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($node_title);
    $this->assertEqual($node->getTitle(), $edit['title[0][value]']);

    // Enable Tools menu as available menu.
    $edit = array(
      'menu_options[main]' => 1,
      'menu_options[tools]' => 1,
      'menu_parent' => 'main:0',
    );
    $this->drupalPostForm('admin/structure/types/manage/page', $edit, t('Save content type'));

    // Create a node.
    $node_title = $this->randomName();
    $edit = array(
      'title[0][value]' => $node_title,
      'body[0][value]' => $this->randomString(),
    );
    $this->drupalPostForm('node/add/page', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($node_title);
    // Assert that there is no link for the node.
    $this->drupalGet('test-page');
    $this->assertNoLink($node_title);

    // Edit the node, enable the menu link setting, but skip the link title.
    $edit = array(
      'menu[enabled]' => 1,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
    // Assert that there is no link for the node.
    $this->drupalGet('test-page');
    $this->assertNoLink($node_title);

    // Edit the node and create a menu link.
    $edit = array(
      'menu[enabled]' => 1,
      'menu[link_title]' => $node_title,
      'menu[weight]' => 17,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
    // Assert that the link exists.
    $this->drupalGet('test-page');
    $this->assertLink($node_title);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertOptionSelected('edit-menu-weight', 17, 'Menu weight correct in edit form');

    // Edit the node and remove the menu link.
    $edit = array(
      'menu[enabled]' => FALSE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
    // Assert that there is no link for the node.
    $this->drupalGet('test-page');
    $this->assertNoLink($node_title);

    // Add a menu link to the Administration menu.
    $item = entity_create('menu_link', array(
      'link_path' => 'node/' . $node->id(),
      'link_title' => $this->randomName(16),
      'menu_name' => 'admin',
    ));
    $item->save();

    // Assert that disabled Administration menu is not shown on the
    // node/$nid/edit page.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertText('Provide a menu link', 'Link in not allowed menu not shown in node edit form');
    // Assert that the link is still in the Administration menu after save.
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
    $link = menu_link_load($item['mlid']);
    $this->assertTrue($link, 'Link in not allowed menu still exists after saving node');

    // Move the menu link back to the Tools menu.
    $item['menu_name'] = 'tools';
    menu_link_save($item);
    // Create a second node.
    $child_node = $this->drupalCreateNode(array('type' => 'article'));
    // Assign a menu link to the second node, being a child of the first one.
    $child_item = entity_create('menu_link', array(
      'link_path' => 'node/'. $child_node->id(),
      'link_title' => $this->randomName(16),
      'plid' => $item['mlid'],
    ));
    $child_item->save();
    // Edit the first node.
    $this->drupalGet('node/'. $node->id() .'/edit');
    // Assert that it is not possible to set the parent of the first node to itself or the second node.
    $this->assertNoOption('edit-menu-parent', 'tools:'. $item['mlid']);
    $this->assertNoOption('edit-menu-parent', 'tools:'. $child_item['mlid']);
    // Assert that unallowed Administration menu is not available in options.
    $this->assertNoOption('edit-menu-parent', 'admin:0');
  }
}
