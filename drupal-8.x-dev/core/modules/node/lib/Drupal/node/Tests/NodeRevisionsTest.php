<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeRevisionsTest.
 */

namespace Drupal\node\Tests;

use Drupal\Core\Language\Language;

/**
 * Tests the node revision functionality.
 */
class NodeRevisionsTest extends NodeTestBase {
  protected $nodes;
  protected $logs;

  public static function getInfo() {
    return array(
      'name' => 'Node revisions by type',
      'description' => 'Create a node with revisions and test viewing, saving, reverting, and deleting revisions for users with access for this content type.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();

    // Create and log in user.
    $web_user = $this->drupalCreateUser(
      array(
        'view page revisions',
        'revert page revisions',
        'delete page revisions',
        'edit any page content',
        'delete any page content'
      )
    );

    $this->drupalLogin($web_user);

    // Create initial node.
    $node = $this->drupalCreateNode();
    $settings = get_object_vars($node);
    $settings['revision'] = 1;
    $settings['isDefaultRevision'] = TRUE;

    $nodes = array();
    $logs = array();

    // Get original node.
    $nodes[] = clone $node;

    // Create three revisions.
    $revision_count = 3;
    for ($i = 0; $i < $revision_count; $i++) {
      $logs[] = $node->log = $this->randomName(32);

      // Create revision with a random title and body and update variables.
      $node->title = $this->randomName();
      $node->body = array(
        'value' => $this->randomName(32),
        'format' => filter_default_format(),
      );
      $node->setNewRevision();
      $node->save();

      $node = node_load($node->id()); // Make sure we get revision information.
      $nodes[] = clone $node;
    }

    $this->nodes = $nodes;
    $this->logs = $logs;
  }

  /**
   * Checks node revision related operations.
   */
  function testRevisions() {
    $nodes = $this->nodes;
    $logs = $this->logs;

    // Get last node for simple checks.
    $node = $nodes[3];

    // Confirm the correct revision text appears on "view revisions" page.
    $this->drupalGet("node/" . $node->id() . "/revisions/" . $node->getRevisionId() . "/view");
    $this->assertText($node->body->value, 'Correct text displays for version.');

    // Confirm the correct log message appears on "revisions overview" page.
    $this->drupalGet("node/" . $node->id() . "/revisions");
    foreach ($logs as $log) {
      $this->assertText($log, 'Log message found.');
    }

    // Confirm that this is the default revision.
    $this->assertTrue($node->isDefaultRevision(), 'Third node revision is the default one.');

    // Confirm that revisions revert properly.
    $this->drupalPostForm("node/" . $node->id() . "/revisions/" . $nodes[1]->getRevisionid() . "/revert", array(), t('Revert'));
    $this->assertRaw(t('@type %title has been reverted back to the revision from %revision-date.',
                        array('@type' => 'Basic page', '%title' => $nodes[1]->label(),
                              '%revision-date' => format_date($nodes[1]->getRevisionCreationTime()))), 'Revision reverted.');
    $reverted_node = node_load($node->id(), TRUE);
    $this->assertTrue(($nodes[1]->body->value == $reverted_node->body->value), 'Node reverted correctly.');

    // Confirm that this is not the default version.
    $node = node_revision_load($node->getRevisionId());
    $this->assertFalse($node->isDefaultRevision(), 'Third node revision is not the default one.');

    // Confirm revisions delete properly.
    $this->drupalPostForm("node/" . $node->id() . "/revisions/" . $nodes[1]->getRevisionId() . "/delete", array(), t('Delete'));
    $this->assertRaw(t('Revision from %revision-date of @type %title has been deleted.',
                        array('%revision-date' => format_date($nodes[1]->getRevisionCreationTime()),
                              '@type' => 'Basic page', '%title' => $nodes[1]->label())), 'Revision deleted.');
    $this->assertTrue(db_query('SELECT COUNT(vid) FROM {node_revision} WHERE nid = :nid and vid = :vid', array(':nid' => $node->id(), ':vid' => $nodes[1]->getRevisionId()))->fetchField() == 0, 'Revision not found.');

    // Set the revision timestamp to an older date to make sure that the
    // confirmation message correctly displays the stored revision date.
    $old_revision_date = REQUEST_TIME - 86400;
    db_update('node_revision')
      ->condition('vid', $nodes[2]->getRevisionId())
      ->fields(array(
        'revision_timestamp' => $old_revision_date,
      ))
      ->execute();
    $this->drupalPostForm("node/" . $node->id() . "/revisions/" . $nodes[2]->getRevisionId() . "/revert", array(), t('Revert'));
    $this->assertRaw(t('@type %title has been reverted back to the revision from %revision-date.', array(
      '@type' => 'Basic page',
      '%title' => $nodes[2]->label(),
      '%revision-date' => format_date($old_revision_date),
    )));

    // Make a new revision and set it to not be default.
    // This will create a new revision that is not "front facing".
    $new_node_revision = clone $node;
    $new_body = $this->randomName();
    $new_node_revision->body->value = $new_body;
    // Save this as a non-default revision.
    $new_node_revision->setNewRevision();
    $new_node_revision->isDefaultRevision = FALSE;
    $new_node_revision->save();

    $this->drupalGet('node/' . $node->id());
    $this->assertNoText($new_body, 'Revision body text is not present on default version of node.');

    // Verify that the new body text is present on the revision.
    $this->drupalGet("node/" . $node->id() . "/revisions/" . $new_node_revision->getRevisionId() . "/view");
    $this->assertText($new_body, 'Revision body text is present when loading specific revision.');

    // Verify that the non-default revision vid is greater than the default
    // revision vid.
    $default_revision = db_select('node', 'n')
      ->fields('n', array('vid'))
      ->condition('nid', $node->id())
      ->execute()
      ->fetchCol();
    $default_revision_vid = $default_revision[0];
    $this->assertTrue($new_node_revision->getRevisionId() > $default_revision_vid, 'Revision vid is greater than default revision vid.');
  }

  /**
   * Checks that revisions are correctly saved without log messages.
   */
  function testNodeRevisionWithoutLogMessage() {
    // Create a node with an initial log message.
    $log = $this->randomName(10);
    $node = $this->drupalCreateNode(array('log' => $log));

    // Save over the same revision and explicitly provide an empty log message
    // (for example, to mimic the case of a node form submitted with no text in
    // the "log message" field), and check that the original log message is
    // preserved.
    $new_title = $this->randomName(10) . 'testNodeRevisionWithoutLogMessage1';

    $node = clone $node;
    $node->title = $new_title;
    $node->log = '';
    $node->setNewRevision(FALSE);

    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertText($new_title, 'New node title appears on the page.');
    $node_revision = node_load($node->id(), TRUE);
    $this->assertEqual($node_revision->log->value, $log, 'After an existing node revision is re-saved without a log message, the original log message is preserved.');

    // Create another node with an initial log message.
    $node = $this->drupalCreateNode(array('log' => $log));

    // Save a new node revision without providing a log message, and check that
    // this revision has an empty log message.
    $new_title = $this->randomName(10) . 'testNodeRevisionWithoutLogMessage2';

    $node = clone $node;
    $node->title = $new_title;
    $node->setNewRevision();
    $node->log = NULL;

    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertText($new_title, 'New node title appears on the page.');
    $node_revision = node_load($node->id(), TRUE);
    $this->assertTrue(empty($node_revision->log->value), 'After a new node revision is saved with an empty log message, the log message for the node is empty.');
  }
}
