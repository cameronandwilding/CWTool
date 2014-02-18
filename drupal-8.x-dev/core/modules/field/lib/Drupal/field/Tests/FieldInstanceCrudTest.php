<?php

/**
 * @file
 * Contains \Drupal\field\Tests\FieldInstanceCrudTest.
 */

namespace Drupal\field\Tests;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\field\FieldException;

class FieldInstanceCrudTest extends FieldUnitTestBase {

  /**
   * The field entity.
   *
   * @var \Drupal\field\Entity\Field
   */
  protected $field;

  /**
   * The field entity definition.
   *
   * @var array
   */
  protected $field_definition;

  /**
   * The field instance entity definition.
   *
   * @var array
   */
  protected $instance_definition;

  public static function getInfo() {
    return array(
      'name' => 'Field instance CRUD tests',
      'description' => 'Create field entities by attaching fields to entities.',
      'group' => 'Field API',
    );
  }

  function setUp() {
    parent::setUp();

    $this->field_definition = array(
      'name' => drupal_strtolower($this->randomName()),
      'entity_type' => 'entity_test',
      'type' => 'test_field',
    );
    $this->field = entity_create('field_entity', $this->field_definition);
    $this->field->save();
    $this->instance_definition = array(
      'field_name' => $this->field->getName(),
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    );
  }

  // TODO : test creation with
  // - a full fledged $instance structure, check that all the values are there
  // - a minimal $instance structure, check all default values are set
  // defer actual $instance comparison to a helper function, used for the two cases above,
  // and for testUpdateFieldInstance

  /**
   * Test the creation of a field instance.
   */
  function testCreateFieldInstance() {
    $instance = entity_create('field_instance', $this->instance_definition);
    $instance->save();

    // Read the configuration. Check against raw configuration data rather than
    // the loaded ConfigEntity, to be sure we check that the defaults are
    // applied on write.
    $config = \Drupal::config('field.instance.' . $instance->id())->get();

    $field_type = \Drupal::service('plugin.manager.field.field_type')->getDefinition($this->field_definition['type']);

    // Check that default values are set.
    $this->assertEqual($config['required'], FALSE, 'Required defaults to false.');
    $this->assertIdentical($config['label'], $this->instance_definition['field_name'], 'Label defaults to field name.');
    $this->assertIdentical($config['description'], '', 'Description defaults to empty string.');

    // Check that default settings are set.
    $this->assertEqual($config['settings'], $field_type['instance_settings'] , 'Default instance settings have been written.');

    // Guarantee that the field/bundle combination is unique.
    try {
      entity_create('field_instance', $this->instance_definition)->save();
      $this->fail(t('Cannot create two instances with the same field / bundle combination.'));
    }
    catch (EntityStorageException $e) {
      $this->pass(t('Cannot create two instances with the same field / bundle combination.'));
    }

    // Check that the specified field exists.
    try {
      $this->instance_definition['field_name'] = $this->randomName();
      entity_create('field_instance', $this->instance_definition)->save();
      $this->fail(t('Cannot create an instance of a non-existing field.'));
    }
    catch (FieldException $e) {
      $this->pass(t('Cannot create an instance of a non-existing field.'));
    }

    // TODO: test other failures.
  }

  /**
   * Test reading back an instance definition.
   */
  function testReadFieldInstance() {
    entity_create('field_instance', $this->instance_definition)->save();

    // Read the instance back.
    $instance = entity_load('field_instance', 'entity_test.' . $this->instance_definition['bundle'] . '.' . $this->instance_definition['field_name']);
    $this->assertTrue($this->instance_definition['field_name'] == $instance->getName(), 'The field was properly read.');
    $this->assertTrue($this->instance_definition['entity_type'] == $instance->entity_type, 'The field was properly read.');
    $this->assertTrue($this->instance_definition['bundle'] == $instance->bundle, 'The field was properly read.');
  }

  /**
   * Test the update of a field instance.
   */
  function testUpdateFieldInstance() {
    entity_create('field_instance', $this->instance_definition)->save();

    // Check that basic changes are saved.
    $instance = entity_load('field_instance', 'entity_test.' . $this->instance_definition['bundle'] . '.' . $this->instance_definition['field_name']);
    $instance->required = !$instance->isRequired();
    $instance->label = $this->randomName();
    $instance->description = $this->randomName();
    $instance->settings['test_instance_setting'] = $this->randomName();
    $instance->save();

    $instance_new = entity_load('field_instance', 'entity_test.' . $this->instance_definition['bundle'] . '.' . $this->instance_definition['field_name']);
    $this->assertEqual($instance->isRequired(), $instance_new->isRequired(), '"required" change is saved');
    $this->assertEqual($instance->getLabel(), $instance_new->getLabel(), '"label" change is saved');
    $this->assertEqual($instance->getDescription(), $instance_new->getDescription(), '"description" change is saved');

    // TODO: test failures.
  }

  /**
   * Test the deletion of a field instance.
   */
  function testDeleteFieldInstance() {
    // TODO: Test deletion of the data stored in the field also.
    // Need to check that data for a 'deleted' field / instance doesn't get loaded
    // Need to check data marked deleted is cleaned on cron (not implemented yet...)

    // Create two instances for the same field so we can test that only one
    // is deleted.
    entity_create('field_instance', $this->instance_definition)->save();
    $another_instance_definition = $this->instance_definition;
    $another_instance_definition['bundle'] .= '_another_bundle';
    entity_create('field_instance', $another_instance_definition)->save();

    // Test that the first instance is not deleted, and then delete it.
    $instance = current(entity_load_multiple_by_properties('field_instance', array('entity_type' => 'entity_test', 'field_name' => $this->instance_definition['field_name'], 'bundle' => $this->instance_definition['bundle'], 'include_deleted' => TRUE)));
    $this->assertTrue(!empty($instance) && empty($instance->deleted), 'A new field instance is not marked for deletion.');
    $instance->delete();

    // Make sure the instance is marked as deleted when the instance is
    // specifically loaded.
    $instance = current(entity_load_multiple_by_properties('field_instance', array('entity_type' => 'entity_test', 'field_name' => $this->instance_definition['field_name'], 'bundle' => $this->instance_definition['bundle'], 'include_deleted' => TRUE)));
    $this->assertTrue(!empty($instance->deleted), 'A deleted field instance is marked for deletion.');

    // Try to load the instance normally and make sure it does not show up.
    $instance = entity_load('field_instance', 'entity_test.' . '.' . $this->instance_definition['bundle'] . '.' . $this->instance_definition['field_name']);
    $this->assertTrue(empty($instance), 'A deleted field instance is not loaded by default.');

    // Make sure the other field instance is not deleted.
    $another_instance = entity_load('field_instance', 'entity_test.' . $another_instance_definition['bundle'] . '.' . $another_instance_definition['field_name']);
    $this->assertTrue(!empty($another_instance) && empty($another_instance->deleted), 'A non-deleted field instance is not marked for deletion.');
  }

  /**
   * Tests the cross deletion behavior between fields and instances.
   */
  function testDeleteFieldInstanceCrossDeletion() {
    $instance_definition_2 = $this->instance_definition;
    $instance_definition_2['bundle'] .= '_another_bundle';

    // Check that deletion of a field deletes its instances.
    $field = $this->field;
    entity_create('field_instance', $this->instance_definition)->save();
    entity_create('field_instance', $instance_definition_2)->save();
    $field->delete();
    $this->assertFalse(field_info_instance('entity_test', $this->instance_definition['bundle'], $field->name));
    $this->assertFalse(field_info_instance('entity_test', $instance_definition_2['bundle'], $field->name));

    // Chack that deletion of the last instance deletes the field.
    $field = entity_create('field_entity', $this->field_definition);
    $field->save();
    $instance = entity_create('field_instance', $this->instance_definition);
    $instance->save();
    $instance_2 = entity_create('field_instance', $instance_definition_2);
    $instance_2->save();
    $instance->delete();
    $this->assertTrue(field_info_field('entity_test', $field->name));
    $instance_2->delete();
    $this->assertFalse(field_info_field('entity_test', $field->name));

    // Check that deletion of all instances of the same field simultaneously
    // deletes the field.
    $field = entity_create('field_entity', $this->field_definition);
    $field->save();
    $instance = entity_create('field_instance', $this->instance_definition);
    $instance->save();
    $instance_2 = entity_create('field_instance', $instance_definition_2);
    $instance_2->save();
    $instance_controller = $this->container->get('entity.manager')->getStorageController('field_instance');
    $instance_controller->delete(array($instance, $instance_2));
    $this->assertFalse(field_info_field('entity_test', $field->name));
  }

}
