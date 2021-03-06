<?php
/**
 * @file
 *
 * Content related helpers.
 */

/**
 * Delete a content type. Remove related variables. Rebuild node-type and menu cache.
 *
 * @param $content_type
 *  Content type name.
 */
function cw_tool_delete_content_type($content_type) {
  node_type_delete($content_type);
  variable_del('node_preview_' . $content_type);
  variable_del('comment_subject_field_' . $content_type);
  node_types_rebuild();
  menu_rebuild();
}

/**
 * Removes unused fields from the Drupal database.
 *
 * Called in implementations of hook_update_N when a field is removed from a
 * feature. This is a wrapper function for crimson_helper_delete_field.
 *
 * @param $fields_to_delete
 *  An array of field names for deletion.
 */
function cw_tool_delete_field_multiple(array $fields_to_delete) {
  // Before we begin, we need to get rid of those pesky temp tables that hang about after a field is deleted.
  _cw_tool_purge_deleted_fields();

  // Loop through each field name and delete each individually.
  foreach ($fields_to_delete as $field_name) {
    field_delete_field($field_name);
    watchdog(__FUNCTION__, 'Deleted the @field_name field from all content type instances.', array('@field_name' => $field_name));
  }

  // Now force a purge of all the deleted tables again.
  _cw_tool_purge_deleted_fields();
}

/**
 * Private sub-method to erase already deleted fields.
 */
function _cw_tool_purge_deleted_fields() {
  $result = db_query("SHOW TABLES LIKE 'field_deleted_%'")->fetchCol();
  foreach ($result as $table) {
    db_drop_table($table);
  }

  db_delete('field_config')->condition('deleted', 1)->execute();
  $num_deleted = db_delete('field_config_instance')
    ->condition('deleted', 1)
    ->execute();

  watchdog(__FUNCTION__, 'Purged @num_deleted deleted fields from the database', array('@num_deleted' => $num_deleted));
}

/**
 * Creates taxonomy terms for the vocabulary.
 *
 * Called in implementations of hook_update_N when a taxonomy is created
 * in a feature.
 *
 * @param $vocabulary
 *  The vocabulary where the new terms will be created.
 *
 * @param $terms
 *  An array of term names to add.
 */
function cw_tool_create_term_multiple($vocabulary, array $terms = array()) {
  // Get the vid of the vocabulary that is loaded.
  $vid = taxonomy_vocabulary_machine_name_load($vocabulary)->vid;

  // Add each term to the vocabulary.
  foreach ($terms as $term_name) {
    // Make sure that the term doesn't already exist. It's possible that we'll
    // need to add terms in more than one module for the emerald project. This
    // makes sure terms don't get duplicated.
    $pre_existing = taxonomy_get_term_by_name($term_name, $vocabulary);
    if (!reset($pre_existing)) {
      $term = (object) array(
        'name' => $term_name,
        'vid' => $vid,
      );
      taxonomy_term_save($term);
    }
  }
}

/**
 * Creates a new field collection entity and attach to a host entity.
 *
 * @param $entity
 *  Host entity object.
 * @param $field_name
 *  Field name string of the field collection (aka bundle).
 * @param $entity_type
 *  Entity type of the host entity. Default: node.
 *
 * @return stdClass|FALSE
 *  Field collection entity object - or FALSE if the operation failed.
 */
function cw_tool_add_field_collection($entity, $field_name, $entity_type = 'node') {
  if (!module_exists('field_collection')) {
    watchdog(__FUNCTION__, 'Module \'field_collection\' is not enabled.', array(), WATCHDOG_ERROR);
    return FALSE;
  }

  $field_collection = entity_create('field_collection_item', array('field_name' => $field_name));
  $field_collection->setHostEntity($entity_type, $entity);
  return $field_collection;
}

/**
 * Delete a field instance from an entity.
 *
 * @param $field_name
 *  Field name.
 * @param $bundle
 *  Bundle name.
 * @param string $entity_type
 *  (Optional) Entity name. Default: node.
 */
function cw_tool_delete_field_instance($field_name, $bundle, $entity_type = 'node') {
  $field = field_read_instance($entity_type, $field_name, $bundle);
  field_delete_instance($field, FALSE);
}

/**
 * Purges all terms from a vocabulary.
 *
 * @param $vocabulary_name
 *  The name of the vocabulary.
 */
function cw_tool_purge_terms($vocabulary_name) {
  $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name);
  $tree = taxonomy_get_tree($vocabulary->vid);

  // We'll assume for now that the vocabulary is flat.
  foreach ($tree as $term) {
    taxonomy_term_delete($term->tid);
  }
}

/**
 * Purges all terms from a vocabulary except specific terms listed.
 * Multiple terms with the same name will also be ignored when listed.
 *
 * @param $vocabulary_name
 *  The name of the vocabulary.
 * @param $terms_to_ignore
 *  The names of the terms to keep in a vocabulary.
 */
function cw_tool_purge_all_terms_except_listed_terms($vocabulary_name, array $terms_to_ignore = array()) {
  // Fetch vocabulary info.
  $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name);

  if (!$vocabulary) {
    watchdog(__FUNCTION__, 'Missing vocabulary: @name', array('@name' => $vocabulary_name), WATCHDOG_ERROR);
    return;
  }

  $tree = taxonomy_get_tree($vocabulary->vid);
  // Delete terms that are not listed as ignored.
  foreach ($tree as $term) {
    if (!in_array($term->name, $terms_to_ignore)) {
      taxonomy_term_delete($term->tid);
    }
  }
}

/**
 * Deletes only specific terms that exist from a vocabulary.
 *
 * @param $vocabulary
 *  The name of the vocabulary.
 * @param $terms
 *  The names of the terms to delete from a vocabulary.
 * @param $force_delete_multiple
 *  Force deletion of multiple terms.
 */
function cw_tool_delete_term_multiple($vocabulary, array $terms = array(), $force_delete_multiple = FALSE) {
  foreach ($terms as $term_name) {
    $existing_terms = taxonomy_get_term_by_name($term_name, $vocabulary);
    // Check if there is more than one term and is a forced deletion or if there is only one term.
    if (!(count($existing_terms) > 1 && !$force_delete_multiple)) {
      foreach ($existing_terms as $existing_term) {
        taxonomy_term_delete($existing_term->tid);
      }
    }
  }
}

/**
 * Determine whether a given account has the requested roles.
 *
 * @param $role_ids
 *  An array of Role IDs.
 * @param $account
 *  The optional user object to check. The default is to check the logged in user.
 * @param $any
 *  A boolean indication whether "any" or "all" roles are required.
 *
 * @return boolean
 *  Whether the user has the required roles.
 */
function cw_tool_user_has_role($role_ids, $account = NULL, $any = TRUE) {
  if (empty($account)) {
    $account = $GLOBALS['user'];
  }

  if (!is_array($role_ids)) {
    $role_ids = array($role_ids);
  }

  $account_role_ids = array_keys($account->roles);
  $common_role_ids = array_intersect($role_ids, $account_role_ids);

  if ($any) {
    if (!empty($common_role_ids)) {
      return TRUE;
    }
  }
  elseif (count($common_role_ids) == count($role_ids)) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Resets the taxonomy (or taxonomies) to be ordered alphabetically.
 *
 * @param $vocabulary_names
 *  A single string or array of vocabulary machine names.
 */
function cw_tool_reset_taxonomy_to_alphabetical($vocabulary_names) {
  $vocabularies = taxonomy_vocabulary_load_multiple(NULL, array('machine_name' => $vocabulary_names));
  $vocabulary_ids = array_keys($vocabularies);
  db_update('taxonomy_term_data')
    ->fields(array('weight' => 0))
    ->condition('vid', $vocabulary_ids)
    ->execute();
}

/**
 * Save fields on an entity instead of saving the whole entity. 10 times faster than saving the entity in average.
 *
 * @param stdClass $entity
 *  Entity.
 * @param $entity_type
 *  Entity type.
 * @param array $field_names
 *  List of field names to be saved on the entity.
 *  These items have to be part of the entity object (either new or existing field). Only the entity has to exist in
 *   the database before the call.
 *  It's also possible to pass an incomplete entity without loading it from the db, if it has the id/revision/bundle
 *   on it:
 * @code
 *    $node = new stdClass();
 *    $node->nid = 1;
 *    $node->vid = 1;
 *    $node->type = 'post';
 *    $node->field_tag[LANGUAGE_NONE][0]['tid'] = 1;
 *    crimson_helper_save_fields_on_entity($node, 'node', array('field_tag'));
 * @endcode
 */
function cw_tool_save_fields_on_entity(stdClass $entity, $entity_type, array $field_names) {
  // Gathering info about the entity.
  list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
  $entity_info = entity_get_info($entity_type);

  // Create dummy object to ease the process.
  $dummy_entity = new stdClass();
  $dummy_entity->{$entity_info['entity keys']['id']} = $id;

  if ($entity_info['entity keys']['revision']) {
    $dummy_entity->{$entity_info['entity keys']['revision']} = $vid;
  }
  if ($entity_info['entity keys']['bundle']) {
    $dummy_entity->{$entity_info['entity keys']['bundle']} = $bundle;
  }

  // Attach fields to the dummy.
  foreach ($field_names as $field_name) {
    $dummy_entity->{$field_name} = $entity->{$field_name};
  }

  // Trigger field API hooks.
  field_attach_presave($entity_type, $dummy_entity);
  field_attach_update($entity_type, $dummy_entity);

  // Invalidate cache for the entity.
  entity_get_controller($entity_type)->resetCache(array($id));
}

/**
 * Purges all terms from a vocabulary.
 *
 * @param string $vocabulary_name
 *  Vocabulary name.
 */
function cw_tool_purge_terms_of_vocabulary($vocabulary_name) {
  // Fetch vocabulary info.
  $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name);

  if (!$vocabulary) {
    watchdog(__FUNCTION__, 'Missing vocabulary: @name', array('@name' => $vocabulary_name), WATCHDOG_ERROR);
    return;
  }

  $tree = taxonomy_get_tree($vocabulary->vid);

  // We'll assume for now that the vocabulary is flat.
  foreach ($tree as $term) {
    taxonomy_term_delete($term->tid);
  }
}

/**
 * Delete a vocabulary and all the terms.
 *
 * @param string $vocabulary_name
 *  Name of the vocabulary.
 */
function cw_tool_delete_vocabulary($vocabulary_name) {
  // Fetch vocabulary info.
  $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name);

  if (!$vocabulary) {
    watchdog(__FUNCTION__, 'Missing vocabulary: @name', array('@name' => $vocabulary_name), WATCHDOG_ERROR);
    return;
  }

  // Delete terms.
  cw_tool_purge_terms_of_vocabulary($vocabulary_name);

  // Delete vocabulary.
  taxonomy_vocabulary_delete($vocabulary->vid);
}

/**
 * Delete a list of vocabularies and all the terms.
 *
 * @param array $vocabulary_names
 *  Names of the vocabularies.
 */
function cw_tool_delete_vocabulary_multiple(array $vocabulary_names) {
  foreach ($vocabulary_names as $vocabulary_name) {
    cw_tool_delete_vocabulary($vocabulary_name);
  }
}

/**
 * Adds menu items to a menu.
 *
 * @param array $menu_items
 *  The menu items added. An associative array formatted as 'link title' => 'link path'.
 * @param $menu_name
 *  The name of the menu the menu items will be added to.
 * @param $weight
 *  The weight set for the first menu item and increments
 *  when there is more than one menu item.
 */
function cw_tool_add_submenus(array $menu_items, $menu_name, $weight = 1) {
  // Create path for each link.
  foreach ($menu_items as $title => $path) {
    // Create menu items in the menu.
    $item = array(
      'link_title' => $title,
      'link_path' => $path,
      'menu_name' => $menu_name,
      'weight' => $weight,
    );

    menu_link_save($item);
    $weight++;
  }
}

/**
 * Get the value of a field on a field collection - when the format of the field-collection data is not sure.
 * For example this happens when you work with node objects through node-api or form-api. Node api gives you object
 * where you can find the field collection id (and you need to load the field collection), form api gives you array
 * where you have already all the field data:
 *
 * Form API:
 *   NODE[FIELD_COLLECTION][LANGUAGE][DELTA] = array(<field data>).
 *
 * Node API:
 *   NODE->[FIELD_COLLECTION][LANGUAGE][DELTA] = array(value => FIELD_COLLECTION_ID).
 *
 * @param $field_collection_item
 *  The field collection item object or array.
 * @param $field_name
 *  Name of the field on the field collection.
 *
 * @return mixed|null
 *  Field value array if exists. NULL otherwise.
 */
function cw_tool_get_value_in_an_object_or_array_field_collection_field($field_collection_item, $field_name) {
  if (isset($field_collection_item['value'])) {
    $field_collection = entity_load_single('field_collection_item', $field_collection_item['value']);
    if (isset($field_collection->{$field_name}[LANGUAGE_NONE][0])) {
      return $field_collection->{$field_name}[LANGUAGE_NONE][0];
    }
  }
  elseif (isset($field_collection_item[$field_name][LANGUAGE_NONE][0])) {
    return $field_collection_item[$field_name][LANGUAGE_NONE][0];
  }

  return NULL;
}

/**
 * Adds a new menu with menu links.
 *
 * @param array $menu
 *  The menu to be created.
 * @param array $menu_items
 *  The menu items added. An associative array formatted as 'link title' => 'link path'.
 * @param $weight
 *  The weight set for the first menu item and increments
 *  when there is more than one menu item.
 */
function cw_tool_add_menu_with_links(array $menu, array $menu_items, $weight = 1) {
  // We can not proceed without at least the menu name.
  if (!isset($menu['menu_name'])) {
    watchdog(__FUNCTION__, 'Menu name not included in menu array.', array(), WATCHDOG_ERROR);
    return;
  }
  $menu_name = $menu['menu_name'];
  // Check that the menu has not been created yet.
  $menu_exists = db_select('menu_custom', 'mc')
    ->fields('mc', array('menu_name'))
    ->condition('menu_name', $menu_name)
    ->execute()
    ->rowCount();
  if ($menu_exists) {
    watchdog(__FUNCTION__, 'Menu @menu_name already exists.', array('@menu_name' => $menu_name), WATCHDOG_ERROR);
    return;
  }
  // Save the menu.
  menu_save($menu);
  // Create the menu links.
  cw_tool_add_submenus($menu_items, $menu_name, $weight);
}

/**
 * Update the region and weight for a block in a given theme.
 *
 * This function will update the region and weight of a block. If no region is
 * specified it will disable the block.
 *
 * @param string $theme
 *  The name of the theme to save block settings to.
 * @param string $module
 *  The module that created the block.
 * @param string $delta
 *  The delta of the block.
 * @param string $region
 *  The region to save the block in.
 * @param int $weight
 *  The weight of the block.
 */
function cw_tool_update_block_region_and_weight($theme, $module, $delta, $region, $weight = 0) {
  if (!module_exists('block')) {
    watchdog(__FUNCTION__, 'Module \'block\' is not enabled.', array(), WATCHDOG_ERROR);
    return;
  }

  // Check that we have been passed and valid region.
  $available_regions = system_region_list($theme);
  if ($region != BLOCK_REGION_NONE && !isset($available_regions[$region])) {
    watchdog(__FUNCTION__, 'Region @region_name not found in theme @theme_name.', array(
      '@region_name' => $region,
      '@theme_name' => $theme
    ), WATCHDOG_ERROR);
    return;
  }
  // Get the block id to update.
  $block_id = db_select('block', 'b')
    ->fields('b', array('bid'))
    ->condition('theme', $theme)
    ->condition('module', $module)
    ->condition('delta', $delta)
    ->execute()
    ->fetchField();
  // Check that a valid block has been found.
  if (is_null($block_id)) {
    watchdog(__FUNCTION__, 'No block found in database from module @module_name, delta @delta in theme @theme_name.', array(
      '@module_name' => $delta,
      '@delta' => $delta,
      '@theme_name' => $theme
    ), WATCHDOG_ERROR);
    return;
  }
  // If block is not being assigned to a region, it is being disabled.
  $block_status = (int) ($region != BLOCK_REGION_NONE);
  // Update block placement.
  db_update('block')
    ->fields(array(
      'region' => $region,
      'weight' => $weight,
      'status' => $block_status,
    ))
    ->condition('bid', $block_id)
    ->execute();
}

/**
 * Gets a menu link from a given path and menu name.
 *
 * @param $path
 *  The link_path of the menu item.
 * @param $menu_name
 *  The name of the menu the link belongs to.
 *
 * @return array
 *  A menu link, with $item['access'] filled and link translated for rendering.
 */
function cw_tool_get_menu_link_from_path_and_menu_name($path, $menu_name) {
  $query = db_select('menu_links', 'ml')
    ->fields('ml', array('mlid'))
    ->condition('ml.link_path', $path)
    ->condition('ml.menu_name', $menu_name)
    ->execute()
    ->fetchCol();
  $mlid = current($query);
  return menu_link_load($mlid);
}

/**
 * Gets a menu link from a given link title and menu name.
 *
 * @param $link_title
 *  The link title of the menu item.
 * @param $menu_name
 *  The name of the menu the link belongs to.
 *
 * @return array
 *  A menu link, with $item['access'] filled and link translated for rendering.
 */
function cw_tool_get_menu_link_from_link_title_and_menu_name($link_title, $menu_name) {
  $query = db_select('menu_links', 'ml')
    ->fields('ml', array('mlid'))
    ->condition('ml.link_title', $link_title)
    ->condition('ml.menu_name', $menu_name)
    ->execute()
    ->fetchCol();
  $mlid = current($query);
  return menu_link_load($mlid);
}

/**
 * Create a node.
 *
 * @param string $title
 *  The title of the node.
 * @param string $type
 *  The machine name of the node type.
 * @param array $additional_information .
 *  An optional associative array contain addition information to add to the
 *  created node. For example field information or menu information.
 * @see https://drupal.org/node/49768
 * @param string $language
 *  The default language of the node.
 * @param int $uid
 *  The user id of the node author.
 *
 * @return stdClass
 *  The created node object.
 */
function cw_tool_create_node($title, $type, array $additional_information = array(), $language = LANGUAGE_NONE, $uid = 0) {
  // Create a new node object.
  $node = new stdClass();
  $node->type = $type;
  $node->is_new = TRUE;

  // Set defaults.
  node_object_prepare($node);

  // Add our given node information to the node object.
  $node->title = $title;
  $node->language = $language;
  if (!empty($additional_information)) {
    foreach ($additional_information as $node_property => $property) {
      $node->$node_property = $property;
    }
  }
  // Node object prepare will attempt to use global $user to add the UID, which
  // will be null if we run through Drush.
  $node->uid = $uid;

  // Prepare node for saving. Note that if $node->author is set to a username here
  // it will override the previous value set for UID.
  node_submit($node);

  // Save node and return.
  node_save($node);
  return $node;
}

/**
 * Create user roles.
 *
 * @param array $user_roles
 *  An array of the user role machine associated with its weight.
 */
function cw_tool_create_user_roles(array $user_roles) {
  foreach ($user_roles as $machine_name => $weight) {
    $role = new stdClass();
    $role->name = $machine_name;
    $role->weight = $weight;
    user_role_save($role);
  }
}

/**
 * Assign permission to user roles.
 *
 * @param $user_role
 *  The user roles to assign permissions to.
 * @param array $user_role_permissions
 *  An array of permissions associated with granted or revoke permission statuses.
 */
function cw_tool_assign_user_permissions_to_user_role($user_role, array $user_role_permissions) {
  // The role to grant the permissions to.
  $user_role = user_role_load_by_name($user_role);

  // Grant permissions.
  if ($user_role->rid) {
    user_role_change_permissions($user_role->rid, $user_role_permissions);
  }
}
