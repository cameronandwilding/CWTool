<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateDblogConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of variables from the dblog module.
 */
class MigrateDblogConfigsTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('dblog');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to dblog.settings.yml',
      'description'  => 'Upgrade variables to dblog.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of dblog variables to dblog.settings.yml.
   */
  public function testBookSettings() {
    $migration = entity_load('migration', 'd6_dblog_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6DblogSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('dblog.settings');
    $this->assertIdentical($config->get('row_limit'), 1000);
  }
}
