<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\TestSqlIdMap.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\id_map\Sql;

/**
 * Defines a SQL ID map for use in tests.
 */
class TestSqlIdMap extends Sql implements \Iterator {

  /**
   * Constructs a TestSqlIdMap object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID for the migration process to do.
   * @param array $plugin_definition
   *   The configuration for the plugin.
   * @param \Drupal\migrate\Entity\MigrationInterface $migration
   *   The migration to do.
   */
  function __construct(Connection $database, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    $this->database = $database;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function getDatabase() {
    return parent::getDatabase();
  }

}
