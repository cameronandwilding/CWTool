<?php

/**
 * @file
 * Definition of Drupal\file\Entity\File.
 */

namespace Drupal\file\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Language\Language;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;

/**
 * Defines the file entity class.
 *
 * @EntityType(
 *   id = "file",
 *   label = @Translation("File"),
 *   controllers = {
 *     "storage" = "Drupal\file\FileStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder"
 *   },
 *   base_table = "file_managed",
 *   entity_keys = {
 *     "id" = "fid",
 *     "label" = "filename",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class File extends ContentEntityBase implements FileInterface {

  /**
   * The plain data values of the contained properties.
   *
   * Define default values.
   *
   * @var array
   */
  protected $values = array(
    'langcode' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => Language::LANGCODE_NOT_SPECIFIED))),
  );

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('fid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilename() {
    return $this->get('filename')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFilename($filename) {
    $this->get('filename')->value = $filename;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileUri() {
    return $this->get('uri')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFileUri($uri) {
    $this->get('uri')->value = $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeType() {
    return $this->get('filemime')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMimeType($mime) {
    $this->get('filemime')->value = $mime;
  }

  /**
   * {@inheritdoc}
   */
  public function getSize() {
    return $this->get('filesize')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSize($size) {
    $this->get('filesize')->value = $size;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPermanent() {
    return $this->get('status')->value == FILE_STATUS_PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function isTemporary() {
    return $this->get('status')->value == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setPermanent() {
    $this->get('status')->value = FILE_STATUS_PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function setTemporary() {
    $this->get('status')->value = 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    // Automatically detect filename if not set.
    if (!isset($values['filename']) && isset($values['uri'])) {
      $values['filename'] = drupal_basename($values['uri']);
    }

    // Automatically detect filemime if not set.
    if (!isset($values['filemime']) && isset($values['uri'])) {
      $values['filemime'] = file_get_mimetype($values['uri']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    $this->changed->value = REQUEST_TIME;
    if (empty($this->created->value)) {
      $this->created->value = REQUEST_TIME;
    }

    $this->setSize(filesize($this->getFileUri()));
    if (!isset($this->langcode->value)) {
      // Default the file's language code to none, because files are language
      // neutral more often than language dependent. Until we have better
      // flexible settings.
      // @todo See http://drupal.org/node/258785 and followups.
      $this->langcode = Language::LANGCODE_NOT_SPECIFIED;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::preDelete($storage_controller, $entities);

    foreach ($entities as $entity) {
      // Delete all remaining references to this file.
      $file_usage = \Drupal::service('file.usage')->listUsage($entity);
      if (!empty($file_usage)) {
        foreach ($file_usage as $module => $usage) {
          \Drupal::service('file.usage')->delete($entity, $module);
        }
      }
      // Delete the actual file. Failures due to invalid files and files that
      // were already deleted are logged to watchdog but ignored, the
      // corresponding file entity will be deleted.
      file_unmanaged_delete($entity->getFileUri());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $fields['fid'] = FieldDefinition::create('integer')
      ->setLabel(t('File ID'))
      ->setDescription(t('The file ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The file UUID.'))
      ->setReadOnly(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The file language code.'));

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the file.'))
      ->setSetting('target_type', 'user');

    $fields['filename'] = FieldDefinition::create('string')
      ->setLabel(t('Filename'))
      ->setDescription(t('Name of the file with no path components.'));

    $fields['uri'] = FieldDefinition::create('uri')
      ->setLabel(t('URI'))
      ->setDescription(t('The URI to access the file (either local or remote).'));

    $fields['filemime'] = FieldDefinition::create('string')
      ->setLabel(t('File MIME type'))
      ->setDescription(t("The file's MIME type."));

    $fields['filesize'] = FieldDefinition::create('integer')
      ->setLabel(t('File size'))
      ->setDescription(t('The size of the file in bytes.'));

    $fields['status'] = FieldDefinition::create('integer')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the file, temporary (0) and permanent (1).'));

    $fields['created'] = FieldDefinition::create('integer')
      ->setLabel(t('Created'))
      ->setDescription(t('The timestamp that the file was created.'));

    $fields['changed'] = FieldDefinition::create('integer')
      ->setLabel(t('Changed'))
      ->setDescription(t('The timestamp that the file was last changed.'));

    return $fields;
  }

}
