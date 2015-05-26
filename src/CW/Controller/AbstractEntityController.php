<?php
/**
 * @file
 *
 * Abstract entity controller.
 *
 * @defgroup cwentity Entity controllers
 * @{
 */

namespace CW\Controller;

use CW\Adapter\FieldAccessor;
use CW\Factory\EntityControllerFactory;
use CW\Model\ObjectHandler;
use CW\Util\FieldUtil;
use CW\Util\LoggerObject;
use EntityMetadataWrapper;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractEntityController
 * @package CW\Controller
 *
 * Provides data (Drupal) access to the data and suppose to keep data specific
 * behavior.
 *
 * Use entity controllers through entity controller factories so instances are
 * stored properly in the cache (identity object containers).
 * @see EntityControllerFactory
 */
abstract class AbstractEntityController extends LoggerObject implements FieldAccessor {

  // On the entity, created and changed timestamps are different sometimes, even
  // if the entity was not updated. We need to check the updated state (being
  // created and changed different) outside of a threshold.
  // Eg.: $isUpdated = $entity->changed > $entity->created + UPDATE_TIMESTAMP_VALIDABILITY_THRESHOLD;
  const UPDATE_TIMESTAMP_VALIDABILITY_THRESHOLD = 2;

  // Param const for:
  /** @see $this->entity() */
  const RELOAD_FORCE = TRUE;
  const RELOAD_IGNORE = FALSE;

  /**
   * Entity type.
   *
   * @var string
   */
  private $entityType;

  /**
   * Entity ID.
   *
   * @var int
   */
  private $entityId;

  /**
   * Data accessor, in order to eliminate coupling with Drupal entity API.
   * Use this for entity operations (CRUD).
   *
   * @var ObjectHandler
   */
  protected $objectHandler;

  /**
   * The entity metadata wrapper object.
   * Use $this->metadata() to access it.
   *
   * @var EntityMetadataWrapper
   */
  private $entityMetadataWrapper;

  /**
   * Drupal object.
   * Use $this->entity() to access it.
   *
   * @var object
   */
  private $entity;

  /**
   * Update flag.
   *
   * @var bool
   */
  private $isUpdated = FALSE;

  /**
   * Constructor.
   *
   * @param \CW\Model\ObjectHandler $objectLoader
   * @param \Psr\Log\LoggerInterface $logger
   * @param string $entity_type
   * @param int|string $entity_id
   */
  public function __construct(ObjectHandler $objectLoader, LoggerInterface $logger, $entity_type, $entity_id) {
    parent::__construct($logger);

    $this->entityType = $entity_type;
    $this->entityId = $entity_id;
    $this->objectHandler = $objectLoader;
  }

  /**
   * Get the entity metadata wrapper of the entity.
   *
   * @return EntityMetadataWrapper
   *
   * @throws Exception
   *  Entity metadata wrapper exception.
   */
  public function metadata() {
    if (!isset($this->entityMetadataWrapper)) {
      $this->entityMetadataWrapper = $this->objectHandler->loadMetadata($this->entityType, $this->entity());
    }

    return $this->entityMetadataWrapper;
  }

  /**
   * Get the Drupal object of the entity.
   *
   * @param bool $forceReload
   *  self::RELOAD_*
   * @return mixed|object
   */
  public function entity($forceReload = self::RELOAD_IGNORE) {
    if ($forceReload === self::RELOAD_FORCE || !isset($this->entity)) {
      if (empty($this->entityId)) {
        return NULL;
      }

      $this->entity = $this->objectHandler->loadSingleEntity($this->entityType, $this->entityId);
    }

    return $this->entity;
  }

  /**
   * Check if the entity object is loaded already.
   * In general this should not be called directly as it's loaded intelligently.
   *
   * @return bool
   */
  public function hasEntityLoaded() {
    return !empty($this->entity);
  }

  /**
   * Sets the Drupal object.
   *
   * @param object $entity
   *  Drupal object.
   */
  public function setEntity($entity) {
    $this->entity = $entity;
  }

  /**
   * Save data to database.
   */
  public function save() {
    $this->logger->info('Entity has been saved {this}', array('this' => $this->__toString()));
    $this->objectHandler->save($this->entityType, $this->entity());
    $this->setClean();
  }

  /**
   * Check if object has change.
   *
   * @return boolean
   */
  public function isDirty() {
    return $this->isUpdated;
  }

  /**
   * Mark as changed.
   */
  public function setDirty() {
    $this->isUpdated = TRUE;
  }

  /**
   * Mask as clean.
   */
  public function setClean() {
    $this->isUpdated = FALSE;
  }

  /**
   * Delete entity permanently.
   *
   * @return mixed
   */
  public function delete() {
    return $this->objectHandler->delete($this->entityType, $this->entityId);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '[' . get_class($this) . ", {$this->entityType}:{$this->entityId}]@" . spl_object_hash($this);
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @return int
   */
  public function getEntityId() {
    return $this->entityId;
  }

  /**
   * Get the entity type the class represents.
   * Throws exception if there is not.
   *
   * This is a helper for other services to be aware of the entity info.
   *
   * @return string
   * @throws \Exception
   */
  public static function getClassEntityType() {
    throw new Exception('Undefined entity type');
  }

  /**
   * Get the entity bundle the class represents.
   * Similar to:
   * @see $this->getClassEntityType()
   *
   * @return string
   * @throws \Exception
   */
  public static function getClassEntityBundle() {
    throw new Exception('Undefined entity bundle');
  }

  /**
   * @param $entity
   * @return bool
   */
  public static function isValidEntity($entity) {
    if (!is_object($entity)) {
      return FALSE;
    }

    try {
      $bundleExpected = static::getClassEntityBundle();
      $entityType = static::getClassEntityType();
      list(,, $bundle) = entity_extract_ids($entityType, $entity);
      return $bundle == $bundleExpected;
    }
    catch (Exception $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fieldValue($fieldName, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$fieldName}[$lang][$idx][$key])) {
      return NULL;
    }
    return $this->entity()->{$fieldName}[$lang][$idx][$key];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldTargetID($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    return $this->fieldValue($fieldName, FieldUtil::KEY_TARGET_ID, $idx, $lang);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldFileID($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    return $this->fieldValue($fieldName, FieldUtil::KEY_FILE_ID, $idx, $lang);
  }

  /**
   * Extract all the values of field items.
   *
   * @param string $field_name
   * @param string $key
   * @param string $lang
   * @return array
   */
  public function multiFieldValues($field_name, $key = FieldUtil::KEY_VALUE, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$field_name}[$lang])) {
      return array();
    }

    $values = array();
    foreach ($this->entity()->{$field_name}[$lang] as $idx => $item) {
      $values[] = $this->fieldValue($field_name, $key, $idx, $lang);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldItem($fieldName, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$fieldName}[$lang][$idx])) {
      return NULL;
    }
    return $this->entity()->{$fieldName}[$lang][$idx];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldItems($fieldName, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$fieldName}[$lang])) {
      return NULL;
    }
    return $this->entity()->{$fieldName}[$lang];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedFileCtrl($fieldName, EntityControllerFactory $entityFactory, $idx = 0, $lang = LANGUAGE_NONE) {
    $fid = $this->fieldValue($fieldName, FieldUtil::KEY_FILE_ID, $idx, $lang);
    if (empty($fid)) {
      return NULL;
    }

    return $entityFactory->initWithId($fid);
  }

  /**
   * Set a single field value.
   *
   * @param string $field_name
   * @param mixed $value
   * @param string $key
   * @param int $idx
   * @param string $lang
   */
  public function setFieldValue($field_name, $value, $key = FieldUtil::KEY_VALUE, $idx = 0, $lang = LANGUAGE_NONE) {
    $this->entity()->{$field_name}[$lang][$idx][$key] = $value;
  }

  /**
   * Updates a field with multiple values.
   * Be careful as it's removing the previous values and only keeping the new
   * ones.
   *
   * @param string $field_name
   * @param array $values
   * @param string $key
   * @param string $lang
   */
  public function setMultiFieldValues($field_name, array $values, $key = FieldUtil::KEY_VALUE, $lang = LANGUAGE_NONE) {
    $this->entity()->{$field_name}[$lang] = array();
    foreach ($values as $value) {
      $this->entity()->{$field_name}[$lang][] = array($key => $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedEntityController($fieldName, EntityControllerFactory $entityControllerFactory, $idx = 0, $lang = LANGUAGE_NONE) {
    if (!($targetID = $this->fieldValue($fieldName, FieldUtil::KEY_TARGET_ID, $idx, $lang))) {
      return NULL;
    }
    return $entityControllerFactory->initWithId($targetID);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldAllReferencedEntityController($fieldName, EntityControllerFactory $factory, $lang = LANGUAGE_NONE) {
    if (!isset($this->entity()->{$fieldName}[$lang])) {
      return array();
    }

    $controllers = array();
    foreach (array_keys($this->entity()->{$fieldName}[$lang]) as $idx) {
      $controllers[] = $this->fieldReferencedEntityController($fieldName, $factory, $idx, $lang);
    }

    return array_filter($controllers);
  }

  /**
   * Get a property of the entity object.
   *
   * @param string $key
   * @return mixed
   */
  public function property($key) {
    return isset($this->entity()->{$key}) ? $this->entity()->{$key} : NULL;
  }

  /**
   * @param string $key
   * @param mixed $value
   */
  public function setProperty($key, $value) {
    $this->entity()->{$key} = $value;
  }

  /**
   * Render a field of the entity.
   *
   * @param string $fieldName
   * @param $display
   *   Can be either:
   *   - The name of a view mode. The field will be displayed according to the
   *     display settings specified for this view mode in the $instance
   *     definition for the field in the entity's bundle.
   *     If no display settings are found for the view mode, the settings for
   *     the 'default' view mode will be used.
   *   - An array of display settings, as found in the 'display' entry of
   *     $instance definitions. The following key/value pairs are allowed:
   *     - label: (string) Position of the label. The default 'field' theme
   *       implementation supports the values 'inline', 'above' and 'hidden'.
   *       Defaults to 'above'.
   *     - type: (string) The formatter to use. Defaults to the
   *       'default_formatter' for the field type, specified in
   *       hook_field_info(). The default formatter will also be used if the
   *       requested formatter is not available.
   *     - settings: (array) Settings specific to the formatter. Defaults to the
   *       formatter's default settings, specified in
   *       hook_field_formatter_info().
   *     - weight: (float) The weight to assign to the renderable element.
   *       Defaults to 0.
   * @param $langcode
   *   (Optional) The language the field values are to be shown in. The site's
   *   current language fallback logic will be applied no values are available
   *   for the language. If no language is provided the current language will be
   *   used.
   * @return string
   *  HTML.
   *
   * @see field_view_field()
   */
  public function fieldRender($fieldName, $display = array(), $langcode = NULL) {
    $fieldView = field_view_field($this->getEntityType(), $this->entity(), $fieldName, $display, $langcode);

    if (empty($fieldView)) {
      return NULL;
    }

    return render($fieldView);
  }

  /**
   * Returns the parent controllers that are referencing the instance
   * controller (~reverse reference finder).
   *
   * @param string $fieldName
   *  Field on the parent entity that referencing this.
   * @param string $entityType
   *  Entity type of the parent entity.
   * @param $bundle
   *  Entity bundle of the parent entity.
   * @param \CW\Factory\EntityControllerFactory $factory
   *  Entity factory for the parents.
   * @return AbstractEntityController[]
   */
  public function fieldReferencedParentEntityControllers($fieldName, $entityType, $bundle, EntityControllerFactory $factory) {
    $efq = new \EntityFieldQuery();
    $efq->entityCondition('entity_type', $entityType);
    $efq->entityCondition('bundle', $bundle);
    $efq->fieldCondition($fieldName, FieldUtil::KEY_TARGET_ID, $this->getEntityId());
    $result = $efq->execute();

    if (empty($result[$entityType])) {
      return array();
    }

    $ctrls = array();
    foreach ($result[$entityType] as $entityID => $record) {
      $ctrls[] = $factory->initWithId($entityID);
    }

    return $ctrls;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldReferencedTaxonomyTermCtrl($fieldName, EntityControllerFactory $entityFactory, $idx = 0, $lang = LANGUAGE_NONE) {
    $tid = $this->fieldValue($fieldName, FieldUtil::KEY_TAXONOMY_ID, $idx, $lang);
    if (empty($tid)) {
      return NULL;
    }

    return $entityFactory->initWithId($tid);
  }
}

/**
 * @}
 */
