<?php

/**
 * @file
 * Definition of Drupal\config\Tests\Storage\FileStorageTest.
 */

namespace Drupal\config\Tests\Storage;

use Drupal\Core\Config\FileStorage;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests FileStorage controller operations.
 */
class FileStorageTest extends ConfigStorageTestBase {
  public static function getInfo() {
    return array(
      'name' => 'FileStorage controller operations',
      'description' => 'Tests FileStorage controller operations.',
      'group' => 'Configuration',
    );
  }

  function setUp() {
    parent::setUp();
    $this->storage = new FileStorage($this->configDirectories[CONFIG_ACTIVE_DIRECTORY]);
    $this->invalidStorage = new FileStorage($this->configDirectories[CONFIG_ACTIVE_DIRECTORY] . '/nonexisting');

    // FileStorage::listAll() requires other configuration data to exist.
    $this->storage->write('system.performance', \Drupal::config('system.performance')->get());
  }

  protected function read($name) {
    $data = file_get_contents($this->storage->getFilePath($name));
    return Yaml::parse($data);
  }

  protected function insert($name, $data) {
    file_put_contents($this->storage->getFilePath($name), $data);
  }

  protected function update($name, $data) {
    file_put_contents($this->storage->getFilePath($name), $data);
  }

  protected function delete($name) {
    unlink($this->storage->getFilePath($name));
  }

  /**
   * Tests the FileStorage::listAll method with a relative and absolute path.
   */
  protected function testlistAll() {
    $expected_files = array(
      'system.module',
      'system.performance',
    );

    $config_files = $this->storage->listAll();
    $this->assertIdentical($config_files, $expected_files, 'Relative path, two config files found.');

    // Initialize FileStorage with absolute file path.
    $absolute_path = realpath($this->configDirectories[CONFIG_ACTIVE_DIRECTORY]);
    $storage_absolute_path = new FileStorage($absolute_path);
    $config_files = $storage_absolute_path->listAll();
    $this->assertIdentical($config_files, $expected_files, 'Absolute path, two config files found.');
  }

}
