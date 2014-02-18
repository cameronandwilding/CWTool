<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFileConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of variables from the File module.
 */
class MigrateFileConfigsTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('file');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to file.settings.yml',
      'description'  => 'Upgrade variables to file.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of file variables to file.settings.yml.
   */
  public function testFileSettings() {
    $migration = entity_load('migration', 'd6_file_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FileSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('file.settings');
    $this->assertIdentical($config->get('description.type'), 'textfield');
    $this->assertIdentical($config->get('description.length'), 128);
    $this->assertIdentical($config->get('icon.directory'), 'sites/default/files/icons');
  }
}
