<?php
/**
 * @file
 * Definition of Drupal\node\Entity\Node.
 */
namespace Drupal\checklist\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
/**
 * Defines the checklist item entity class.
 *
 * @EntityType(
 *   id = "node",
 *   label = @Translation("Content"),
 *   bundle_label = @Translation("Content type"),
 *   controllers = {
 *     "storage" = "Drupal\node\NodeStorageController",
 *     "view_builder" = "Drupal\node\NodeViewBuilder",
 *     "access" = "Drupal\node\NodeAccessController",
 *     "form" = {
 *       "default" = "Drupal\node\NodeFormController",
 *       "delete" = "Drupal\node\Form\NodeDeleteForm",
 *       "edit" = "Drupal\node\NodeFormController"
 *     },
 *     "translation" = "Drupal\node\NodeTranslationController"
 *   },
 *   base_table = "node",
 *   data_table = "node_field_data",
 *   revision_table = "node_revision",
 *   revision_data_table = "node_field_revision",
 *   uri_callback = "node_uri",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "nid",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "node_type",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "node.view",
 *     "edit-form" = "node.page_edit",
 *     "version-history" = "node.revision_overview",
 *     "admin-form" = "node.type_edit"
 *   }
 * )
 */
class ChecklistItem extends ContentEntityBase implements NodeInterface {
  // ...
}
?>