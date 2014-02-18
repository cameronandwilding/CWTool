<?php

/**
 * @file
 * Contains \Drupal\Core\Field\FieldItemListInterface.
 */

namespace Drupal\Core\Field;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\TypedData\ListInterface;

/**
 * Interface for fields, being lists of field items.
 *
 * This interface must be implemented by every entity field, whereas contained
 * field items must implement the FieldItemInterface.
 * Some methods of the fields are delegated to the first contained item, in
 * particular get() and set() as well as their magic equivalences.
 *
 * Optionally, a typed data object implementing
 * Drupal\Core\TypedData\TypedDataInterface may be passed to
 * ArrayAccess::offsetSet() instead of a plain value.
 *
 * When implementing this interface which extends Traversable, make sure to list
 * IteratorAggregate or Iterator before this interface in the implements clause.
 */
interface FieldItemListInterface extends ListInterface, AccessibleInterface {

  /**
   * Gets the entity that field belongs to.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity object.
   */
  public function getEntity();

  /**
   * Sets the langcode of the field values held in the object.
   *
   * @param string $langcode
   *   The langcode.
   */
  public function setLangcode($langcode);

  /**
   * Gets the langcode of the field values held in the object.
   *
   * @return $langcode
   *   The langcode.
   */
  public function getLangcode();

  /**
   * Gets the field definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The field definition.
   */
  public function getFieldDefinition();

  /**
   * Returns the array of field settings.
   *
   * @return array
   *   An array of key/value pairs.
   */
  public function getSettings();

  /**
   * Returns the value of a given field setting.
   *
   * @param string $setting_name
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   */
  public function getSetting($setting_name);

  /**
   * Contains the default access logic of this field.
   *
   * See \Drupal\Core\Entity\EntityAccessControllerInterface::fieldAccess() for
   * the parameter documentation.
   *
   * @return bool
   *   TRUE if access to this field is allowed per default, FALSE otherwise.
   */
  public function defaultAccess($operation = 'view', AccountInterface $account = NULL);

  /**
   * Filters out empty field items and re-numbers the item deltas.
   */
  public function filterEmptyItems();

  /**
   * Magic method: Gets a property value of to the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__get()
   */
  public function __get($property_name);

  /**
   * Magic method: Sets a property value of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__set()
   */
  public function __set($property_name, $value);

  /**
   * Magic method: Determines whether a property of the first field item is set.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__isset()
   */
  public function __isset($property_name);

  /**
   * Magic method: Unsets a property of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__unset()
   */
  public function __unset($property_name);

  /**
   * Gets the definition of a property of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::getPropertyDefinition()
   */
  public function getPropertyDefinition($name);

  /**
   * Gets an array of property definitions of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::getPropertyDefinitions()
   */
  public function getPropertyDefinitions();

  /**
   * Defines custom presave behavior for field values.
   *
   * This method is called before either insert() or update() methods, and
   * before values are written into storage.
   */
  public function preSave();

  /**
   * Defines custom insert behavior for field values.
   *
   * This method is called after the save() method, and before values are
   * written into storage.
   */
  public function insert();

  /**
   * Defines custom update behavior for field values.
   *
   * This method is called after the save() method, and before values are
   * written into storage.
   */
  public function update();

  /**
   * Defines custom delete behavior for field values.
   *
   * This method is called during the process of deleting an entity, just before
   * values are deleted from storage.
   */
  public function delete();

  /**
   * Defines custom revision delete behavior for field values.
   *
   * This method is called from during the process of deleting an entity
   * revision, just before the field values are deleted from storage. It is only
   * called for entity types that support revisioning.
   */
  public function deleteRevision();

}
