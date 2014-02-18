<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Entity\EntityFieldDefaultValueTest.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\Core\Language\Language;
use Drupal\Component\Uuid\Uuid;
use Drupal\Component\Utility\String;

/**
 * Tests Entity API default field value functionality.
 */
class EntityFieldDefaultValueTest extends EntityUnitTestBase  {

  /**
   * The UUID object to be used for generating UUIDs.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  public static function getInfo() {
    return array(
      'name' => 'Entity Field Default Value',
      'description' => 'Tests default values for entity fields.',
      'group' => 'Entity API',
    );
  }

  public function setUp() {
    parent::setUp();
    // Initiate the generator object.
    $this->uuid = $this->container->get('uuid');
  }

  /**
   * Tests default values on entities and fields.
   */
  public function testDefaultValues() {
    // All entity variations have to have the same results.
    foreach (entity_test_entity_types() as $entity_type) {
      $this->assertDefaultValues($entity_type);
    }
  }

  /**
   * Executes a test set for a defined entity type.
   *
   * @param string $entity_type
   *   The entity type to run the tests with.
   */
  protected function assertDefaultValues($entity_type) {
    $entity = entity_create($entity_type);
    $this->assertEqual($entity->langcode->value, Language::LANGCODE_NOT_SPECIFIED, String::format('%entity_type: Default language', array('%entity_type' => $entity_type)));
    $this->assertTrue(Uuid::isValid($entity->uuid->value), String::format('%entity_type: Default UUID', array('%entity_type' => $entity_type)));
    $this->assertEqual($entity->name->getValue(), array(0 => array('value' => NULL)), 'Field has one empty value by default.');
  }
}
