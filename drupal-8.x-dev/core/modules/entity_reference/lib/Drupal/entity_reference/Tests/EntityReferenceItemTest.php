<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Tests\EntityReferenceItemTest.
 */

namespace Drupal\entity_reference\Tests;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Language\Language;
use Drupal\field\Tests\FieldUnitTestBase;

/**
 * Tests the new entity API for the entity reference field type.
 */
class EntityReferenceItemTest extends FieldUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_reference', 'taxonomy', 'options', 'text', 'filter');

  /**
   * The taxonomy vocabulary to test with.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * The taxonomy term to test with.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $term;

  public static function getInfo() {
    return array(
      'name' => 'Entity Reference field item',
      'description' => 'Tests using entity fields of the entity reference field type.',
      'group' => 'Entity Reference',
    );
  }

  /**
   * Sets up the test.
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('taxonomy', 'taxonomy_term_data');
    $this->installSchema('taxonomy', 'taxonomy_term_hierarchy');

    $this->vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $this->vocabulary->save();

    $this->term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $this->vocabulary->id(),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $this->term->save();

    // Use the util to create an instance.
    entity_reference_create_instance('entity_test', 'entity_test', 'field_test_taxonomy_term', 'Test content entity reference', 'taxonomy_term');
    entity_reference_create_instance('entity_test', 'entity_test', 'field_test_taxonomy_vocabulary', 'Test config entity reference', 'taxonomy_vocabulary');
  }

  /**
   * Tests the entity reference field type for referencing content entities.
   */
  public function testContentEntityReferenceItem() {
    $tid = $this->term->id();

    // Just being able to create the entity like this verifies a lot of code.
    $entity = entity_create('entity_test');
    $entity->field_test_taxonomy_term->target_id = $tid;
    $entity->name->value = $this->randomName();
    $entity->save();

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue($entity->field_test_taxonomy_term instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_test_taxonomy_term[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_test_taxonomy_term->target_id, $tid);
    $this->assertEqual($entity->field_test_taxonomy_term->entity->name->value, $this->term->name->value);
    $this->assertEqual($entity->field_test_taxonomy_term->entity->id(), $tid);
    $this->assertEqual($entity->field_test_taxonomy_term->entity->uuid(), $this->term->uuid());

    // Change the name of the term via the reference.
    $new_name = $this->randomName();
    $entity->field_test_taxonomy_term->entity->name = $new_name;
    $entity->field_test_taxonomy_term->entity->save();
    // Verify it is the correct name.
    $term = entity_load('taxonomy_term', $tid);
    $this->assertEqual($term->name->value, $new_name);

    // Make sure the computed term reflects updates to the term id.
    $term2 = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $this->term->bundle(),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $term2->save();

    $entity->field_test_taxonomy_term->target_id = $term2->id();
    $this->assertEqual($entity->field_test_taxonomy_term->entity->id(), $term2->id());
    $this->assertEqual($entity->field_test_taxonomy_term->entity->name->value, $term2->name->value);

    // Delete terms so we have nothing to reference and try again
    $term->delete();
    $term2->delete();
    $entity = entity_create('entity_test', array('name' => $this->randomName()));
    $entity->save();
  }

  /**
   * Tests the entity reference field type for referencing config entities.
   */
  public function testConfigEntityReferenceItem() {
    $referenced_entity_id = $this->vocabulary->id();

    // Just being able to create the entity like this verifies a lot of code.
    $entity = entity_create('entity_test');
    $entity->field_test_taxonomy_vocabulary->target_id = $referenced_entity_id;
    $entity->name->value = $this->randomName();
    $entity->save();

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue($entity->field_test_taxonomy_vocabulary instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_test_taxonomy_vocabulary[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->target_id, $referenced_entity_id);
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->entity->name, $this->vocabulary->name);
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->entity->id(), $referenced_entity_id);
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->entity->uuid(), $this->vocabulary->uuid());

    // Change the name of the term via the reference.
    $new_name = $this->randomName();
    $entity->field_test_taxonomy_vocabulary->entity->name = $new_name;
    $entity->field_test_taxonomy_vocabulary->entity->save();
    // Verify it is the correct name.
    $term = entity_load('taxonomy_vocabulary', $referenced_entity_id);
    $this->assertEqual($term->name, $new_name);

    // Make sure the computed term reflects updates to the term id.
    $vocabulary2 = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $vocabulary2->save();

    $entity->field_test_taxonomy_vocabulary->target_id = $vocabulary2->id();
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->entity->id(), $vocabulary2->id());
    $this->assertEqual($entity->field_test_taxonomy_vocabulary->entity->name, $vocabulary2->name);

    // Delete terms so we have nothing to reference and try again
    $this->vocabulary->delete();
    $vocabulary2->delete();
    $entity = entity_create('entity_test', array('name' => $this->randomName()));
    $entity->save();
  }

}
