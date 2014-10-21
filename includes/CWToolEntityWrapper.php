<?php
/**
 * @file
 *
 * Basic logical abstraction of a Drupal entity. Caches loaded entities and provide a facade to access Drupal
 * behaviors and the entity metadata wrapper items.
 *
 * Examples:
 *
 * - instantiating a node:
 * @code
 * $node = EntityWrapper::getOrCreate('node', 123, 'EntityWrapper');
 * @endcode
 *
 * - getting the node object:
 * @code
 * $node->getDrupalObject();
 * @endcode
 *
 * - accessing a single field value:
 * @code
 * if ($item = $node->field_foobar) {
 *   echo $item->value();
 * }
 * @endcode
 *
 * - getting a multivalue field values:
 * @code
 * foreach ($node->field_names as $item) {
 *   echo $item->value();
 * }
 * @endcode
 *
 * - suggested subclassing:
 * @code
 * class MyType extends EntityWrapper {
 *   public static function createFromMyID($my_id) {
 *     return self::getOrCreate('my_type', $my_id, __CLASS__);
 *   }
 *   public function myLogic() {
 *     // do my stuff here.
 *   }
 * }
 * @endcode
 */

/**
 * Class CWToolEntityWrapper
 */
class CWToolEntityWrapper {

  /**
   * Instance cache.
   *
   * @var array
   */
  private static $cache = array();

  /**
   * Entity type.
   *
   * @var string
   */
  public $entityType;

  /**
   * Entity ID.
   *
   * @var int
   */
  public $entityID;

  /**
   * The entity metadata wrapper object.
   * Use $this->getEntityMetadataWrapper() to access it.
   *
   * @var EntityMetadataWrapper
   */
  private $entityMetadataWrapper;

  /**
   * Drupal object.
   * Use $this->getDrupalObject() to access it.
   *
   * @var stdClass
   */
  private $drupalObject;

  /**
   * Constructor.
   *
   * @param string $entity_type
   *  Entity type.
   * @param string $entity_id
   *  Entity ID.
   */
  protected function __construct($entity_type, $entity_id) {
    $this->entityType = $entity_type;
    $this->entityID = $entity_id;
  }

  /**
   * Primal factory to create an instance.
   *
   * @param $entity_type
   *  Entity type string.
   * @param $entity_id
   *  Entity ID.
   * @param $subclass_name
   *  Name of the class to create. Usually just __CLASS__ (See sub-classing example in the file header doc block).
   * @return 
EntityWrapper
   */
  protected static function getOrCreate($entity_type, $entity_id, $subclass_name) {
    if (!isset(self::$cache[$entity_type][$entity_id]) && is_subclass_of($subclass_name, __CLASS__)) {
      $instance = new $subclass_name($entity_type, $entity_id);
      self::$cache[$entity_type][$entity_id] = $instance;
    }

    return self::$cache[$entity_type][$entity_id];
  }

  /**
   * Get the entity metadata wrapper of the entity.
   *
   * @return EntityMetadataWrapper
   *
   * @throws Exception
   *  Entity metadata wrapper exception.
   */
  public function getEntityMetadataWrapper() {
    if (!isset($this->entityMetadataWrapper)) {
      $this->entityMetadataWrapper = entity_metadata_wrapper($this->entityType, $this->getDrupalObject());
    }

    return $this->entityMetadataWrapper;
  }

  /**
   * Get the Drupal object of the entity.
   *
   * @return mixed|stdClass
   */
  public function getDrupalObject() {
    if (!isset($this->drupalObject)) {
      $this->drupalObject = entity_load_single($this->entityType, $this->entityID);
    }

    return $this->drupalObject;
  }

  /**
   * Sets the Drupal object.
   *
   * @param stdClass $drupal_object
   *  Drupal object.
   */
  public function setDrupalObject(stdClass $drupal_object) {
    $this->drupalObject = $drupal_object;
  }

  /**
   * Implements self::__get().
   *
   * @throws Exception
   *  Entity metadata wrapper exception.
   */
  public function __get($name) {
    // Proxy magic getter towards the entity metadata wrapper.
    return $this->getEntityMetadataWrapper()->{$name};
  }

  /**
   * Implements self::__set().
   *
   * @throws Exception
   *  Entity metadata wrapper exception.
   */
  public function __set($name, $value) {
    // Proxy magic setter towards the entity metadata wrapper.
    $this->getEntityMetadataWrapper()->__set($name, $value);
  }

}
