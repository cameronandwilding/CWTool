<?php

/**
 * @file
 * Contains \Drupal\config\Tests\ConfigSingleImportExportTest.
 */

namespace Drupal\config\Tests;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the user interface for importing/exporting a single configuration.
 */
class ConfigSingleImportExportTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('config', 'config_test');

  public static function getInfo() {
    return array(
      'name' => 'Configuration Single Import/Export UI',
      'description' => 'Tests the user interface for importing/exporting a single configuration.',
      'group' => 'Configuration',
    );
  }

  /**
   * Tests importing a single configuration file.
   */
  public function testImport() {
    $storage = \Drupal::entityManager()->getStorageController('config_test');
    $uuid = \Drupal::service('uuid');

    $this->drupalLogin($this->drupalCreateUser(array('import configuration')));
    $import = <<<EOD
label: First
weight: 0
style: ''
status: '1'
EOD;
    $edit = array(
      'config_type' => 'config_test',
      'import' => $import,
    );
    // Attempt an import with a missing ID.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertText(t('Missing ID key "@id_key" for this @entity_type import.', array('@id_key' => 'id', '@entity_type' => 'Test configuration')));

    // Perform an import with no specified UUID and a unique ID.
    $this->assertNull($storage->load('first'));
    $edit['import'] = "id: first\n" . $edit['import'];
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertRaw(t('Are you sure you want to create new %name @type?', array('%name' => 'first', '@type' => 'test configuration')));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    $entity = $storage->load('first');
    $this->assertIdentical($entity->label(), 'First');
    $this->assertIdentical($entity->id(), 'first');
    $this->assertTrue($entity->status());
    $this->assertRaw(t('The @entity_type %label was imported.', array('@entity_type' => 'config_test', '%label' => $entity->label())));

    // Attempt an import with an existing ID but missing UUID.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertText(t('An entity with this machine name already exists but the import did not specify a UUID.'));

    // Attempt an import with a mismatched UUID and existing ID.
    $edit['import'] .= "\nuuid: " . $uuid->generate();
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertText(t('An entity with this machine name already exists but the UUID does not match.'));

    // Perform an import with a unique ID and UUID.
    $import = <<<EOD
id: second
label: Second
weight: 0
style: ''
status: '0'
EOD;
    $edit = array(
      'config_type' => 'config_test',
      'import' => $import,
    );
    $second_uuid = $uuid->generate();
    $edit['import'] .= "\nuuid: " . $second_uuid;
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertRaw(t('Are you sure you want to create new %name @type?', array('%name' => 'second', '@type' => 'test configuration')));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    $entity = $storage->load('second');
    $this->assertRaw(t('The @entity_type %label was imported.', array('@entity_type' => 'config_test', '%label' => $entity->label())));
    $this->assertIdentical($entity->label(), 'Second');
    $this->assertIdentical($entity->id(), 'second');
    $this->assertFalse($entity->status());
    $this->assertIdentical($entity->uuid(), $second_uuid);
  }

  /**
   * Tests importing a simple configuration file.
   */
  public function testImportSimpleConfiguration() {
    $this->drupalLogin($this->drupalCreateUser(array('import configuration')));
    $yaml = new Yaml();
    $config = \Drupal::config('system.site')->set('name', 'Test simple import');
    $edit = array(
      'config_type' => 'system.simple',
      'config_name' => $config->getName(),
      'import' => $yaml->dump($config->get()),
    );
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->assertRaw(t('Are you sure you want to update the %name @type?', array('%name' => $config->getName(), '@type' => 'simple configuration')));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    $this->drupalGet('/');
    $this->assertText('Test simple import');
  }

  /**
   * Tests exporting a single configuration file.
   */
  public function testExport() {
    $this->drupalLogin($this->drupalCreateUser(array('export configuration')));

    $this->drupalGet('admin/config/development/configuration/single/export/system.simple');
    $this->assertFieldByXPath('//select[@name="config_type"]//option', t('Date format'), 'The date format entity type is selected when specified in the URL.');
    // Spot check several known simple configuration files.
    $element = $this->xpath('//select[@name="config_name"]');
    $options = $this->getAllOptions($element[0]);
    $expected_options = array('filter.settings', 'system.site', 'user.settings');
    foreach ($options as &$option) {
      $option = (string) $option;
    }
    $this->assertIdentical($expected_options, array_intersect($expected_options, $options), 'The expected configuration files are listed.');

    $this->drupalGet('admin/config/development/configuration/single/export/system.simple/system.image');
    $this->assertFieldByXPath('//textarea[@name="export"]', "toolkit: gd\n", 'The expected system configuration is displayed.');

    $this->drupalGet('admin/config/development/configuration/single/export/date_format');
    $this->assertFieldByXPath('//select[@name="config_type"]//option', t('Date format'), 'The date format entity type is selected when specified in the URL.');

    $this->drupalGet('admin/config/development/configuration/single/export/date_format/fallback');
    $this->assertFieldByXPath('//select[@name="config_name"]//option', t('Fallback date format'), 'The fallback date format config entity is selected when specified in the URL.');

    $fallback_date = \Drupal::entityManager()->getStorageController('date_format')->load('fallback');
    $data = \Drupal::service('config.storage')->encode($fallback_date->getExportProperties());
    $this->assertFieldByXPath('//textarea[@name="export"]', $data, 'The fallback date format config entity export code is displayed.');
  }

}
