<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\TypedDataManager.
 */

namespace Drupal\Core\TypedData;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\String;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\TypedData\Validation\MetadataFactory;
use Drupal\Core\Validation\ConstraintManager;
use Drupal\Core\Validation\DrupalTranslator;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

/**
 * Manages data type plugins.
 */
class TypedDataManager extends DefaultPluginManager {

  /**
   * The validator used for validating typed data.
   *
   * @var \Symfony\Component\Validator\ValidatorInterface
   */
  protected $validator;

  /**
   * The validation constraint manager to use for instantiating constraints.
   *
   * @var \Drupal\Core\Validation\ConstraintManager
   */
  protected $constraintManager;

  /**
   * An array of typed data property prototypes.
   *
   * @var array
   */
  protected $prototypes = array();

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    $this->alterInfo($module_handler, 'data_type_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'typed_data_types_plugins');

    parent::__construct('Plugin/DataType', $namespaces, 'Drupal\Core\TypedData\Annotation\DataType');
  }

  /**
   * Instantiates a typed data object.
   *
   * @param string $data_type
   *   The data type, for which a typed object should be instantiated.
   * @param array $configuration
   *   The plugin configuration array, i.e. an array with the following keys:
   *   - data definition: The data definition object, i.e. an instance of
   *     \Drupal\Core\TypedData\DataDefinitionInterface.
   *   - name: (optional) If a property or list item is to be created, the name
   *     of the property or the delta of the list item.
   *   - parent: (optional) If a property or list item is to be created, the
   *     parent typed data object implementing either the ListInterface or the
   *     ComplexDataInterface.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The instantiated typed data object.
   */
  public function createInstance($data_type, array $configuration) {
    $data_definition = $configuration['data_definition'];
    $type_definition = $this->getDefinition($data_type);

    if (!isset($type_definition)) {
      throw new \InvalidArgumentException(format_string('Invalid data type %plugin_id has been given.', array('%plugin_id' => $data_type)));
    }

    // Allow per-data definition overrides of the used classes, i.e. take over
    // classes specified in the type definition.
    $class = $data_definition->getClass();
    $class = isset($class) ? $class : $type_definition['class'];

    if (!isset($class)) {
      throw new PluginException(sprintf('The plugin (%s) did not specify an instance class.', $data_type));
    }
    return new $class($data_definition, $configuration['name'], $configuration['parent']);
  }

  /**
   * Creates a new typed data object instance.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition of the typed data object. For backwards-compatibility
   *   an array representation of the data definition may be passed also.
   * @param mixed $value
   *   (optional) The data value. If set, it has to match one of the supported
   *   data type format as documented for the data type classes.
   * @param string $name
   *   (optional) If a property or list item is to be created, the name of the
   *   property or the delta of the list item.
   * @param mixed $parent
   *   (optional) If a property or list item is to be created, the parent typed
   *   data object implementing either the ListInterface or the
   *   ComplexDataInterface.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The instantiated typed data object.
   *
   * @see \Drupal::typedDataManager()
   * @see \Drupal\Core\TypedData\TypedDataManager::getPropertyInstance()
   * @see \Drupal\Core\TypedData\Plugin\DataType\Integer
   * @see \Drupal\Core\TypedData\Plugin\DataType\Float
   * @see \Drupal\Core\TypedData\Plugin\DataType\String
   * @see \Drupal\Core\TypedData\Plugin\DataType\Boolean
   * @see \Drupal\Core\TypedData\Plugin\DataType\Duration
   * @see \Drupal\Core\TypedData\Plugin\DataType\Date
   * @see \Drupal\Core\TypedData\Plugin\DataType\Uri
   * @see \Drupal\Core\TypedData\Plugin\DataType\Binary
   */
  public function create(DataDefinitionInterface $definition, $value = NULL, $name = NULL, $parent = NULL) {
    $typed_data = $this->createInstance($definition->getDataType(), array(
      'data_definition' => $definition,
      'name' => $name,
      'parent' => $parent,
    ));
    if (isset($value)) {
      $typed_data->setValue($value, FALSE);
    }
    return $typed_data;
  }

  /**
   * Implements \Drupal\Component\Plugin\PluginManagerInterface::getInstance().
   *
   * @param array $options
   *   An array of options with the following keys:
   *   - object: The parent typed data object, implementing the
   *     TypedDataInterface and either the ListInterface or the
   *     ComplexDataInterface.
   *   - property: The name of the property to instantiate, or the delta of the
   *     the list item to instantiate.
   *   - value: The value to set. If set, it has to match one of the supported
   *     data type formats as documented by the data type classes.
   *
   * @throws \InvalidArgumentException
   *   If the given property is not known, or the passed object does not
   *   implement the ListInterface or the ComplexDataInterface.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The new property instance.
   *
   * @see \Drupal\Core\TypedData\TypedDataManager::getPropertyInstance()
   */
  public function getInstance(array $options) {
    return $this->getPropertyInstance($options['object'], $options['property'], $options['value']);
  }

  /**
   * Get a typed data instance for a property of a given typed data object.
   *
   * This method will use prototyping for fast and efficient instantiation of
   * many property objects with the same property path; e.g.,
   * when multiple comments are used comment_body.0.value needs to be
   * instantiated very often.
   * Prototyping is done by the root object's data type and the given
   * property path, i.e. all property instances having the same property path
   * and inheriting from the same data type are prototyped.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $object
   *   The parent typed data object, implementing the TypedDataInterface and
   *   either the ListInterface or the ComplexDataInterface.
   * @param string $property_name
   *   The name of the property to instantiate, or the delta of an list item.
   * @param mixed $value
   *   (optional) The data value. If set, it has to match one of the supported
   *   data type formats as documented by the data type classes.
   *
   * @throws \InvalidArgumentException
   *   If the given property is not known, or the passed object does not
   *   implement the ListInterface or the ComplexDataInterface.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The new property instance.
   *
   * @see \Drupal\Core\TypedData\TypedDataManager::create()
   */
  public function getPropertyInstance(TypedDataInterface $object, $property_name, $value = NULL) {
    $definition = $object->getRoot()->getDefinition();
    // If the definition is a list, we need to look at the data type and the
    // settings of its item definition.
    if ($definition instanceof ListDefinition) {
      $definition = $definition->getItemDefinition();
    }
    $key = $definition->getDataType();
    if ($settings = $definition->getSettings()) {
      $key .= ':' . implode(',', $settings);
    }
    $key .= ':' . $object->getPropertyPath() . '.';
    // If we are creating list items, we always use 0 in the key as all list
    // items look the same.
    $key .= is_numeric($property_name) ? 0 : $property_name;

    // Make sure we have a prototype. Then, clone the prototype and set object
    // specific values, i.e. the value and the context.
    if (!isset($this->prototypes[$key]) || !$key) {
      // Create the initial prototype. For that we need to fetch the definition
      // of the to be created property instance from the parent.
      if ($object instanceof ComplexDataInterface) {
        $definition = $object->getPropertyDefinition($property_name);
      }
      elseif ($object instanceof ListInterface) {
        $definition = $object->getItemDefinition();
      }
      else {
        throw new \InvalidArgumentException("The passed object has to either implement the ComplexDataInterface or the ListInterface.");
      }
      // Make sure we have got a valid definition.
      if (!$definition) {
        throw new \InvalidArgumentException('Property ' . String::checkPlain($property_name) . ' is unknown.');
      }
      // Now create the prototype using the definition, but do not pass the
      // given value as it will serve as prototype for any further instance.
      $this->prototypes[$key] = $this->create($definition, NULL, $property_name, $object);
    }

    // Clone from the prototype, then update the parent relationship and set the
    // data value if necessary.
    $property = clone $this->prototypes[$key];
    $property->setContext($property_name, $object);
    if (isset($value)) {
      $property->setValue($value, FALSE);
    }
    return $property;
  }

  /**
   * Sets the validator for validating typed data.
   *
   * @param \Symfony\Component\Validator\ValidatorInterface $validator
   *   The validator object to set.
   */
  public function setValidator(ValidatorInterface $validator) {
    $this->validator = $validator;
  }

  /**
   * Gets the validator for validating typed data.
   *
   * @return \Symfony\Component\Validator\ValidatorInterface
   *   The validator object.
   */
  public function getValidator() {
    if (!isset($this->validator)) {
      $this->validator = Validation::createValidatorBuilder()
        ->setMetadataFactory(new MetadataFactory())
        ->setTranslator(new DrupalTranslator())
        ->getValidator();
    }
    return $this->validator;
  }

  /**
   * Sets the validation constraint manager.
   *
   * The validation constraint manager is used to instantiate validation
   * constraint plugins.
   *
   * @param \Drupal\Core\Validation\ConstraintManager
   *   The constraint manager to set.
   */
  public function setValidationConstraintManager(ConstraintManager $constraintManager) {
    $this->constraintManager = $constraintManager;
  }

  /**
   * Gets the validation constraint manager.
   *
   * @return \Drupal\Core\Validation\ConstraintManager
   *   The constraint manager.
   */
  public function getValidationConstraintManager() {
    return $this->constraintManager;
  }

  /**
   * Gets configured constraints from a data definition.
   *
   * Any constraints defined for the data type, i.e. below the 'constraint' key
   * of the type's plugin definition, or constraints defined below the data
   * definition's constraint' key are taken into account.
   *
   * Constraints are defined via an array, having constraint plugin IDs as key
   * and constraint options as values, e.g.
   * @code
   * $constraints = array(
   *   'Range' => array('min' => 5, 'max' => 10),
   *   'NotBlank' => array(),
   * );
   * @endcode
   * Options have to be specified using another array if the constraint has more
   * than one or zero options. If it has exactly one option, the value should be
   * specified without nesting it into another array:
   * @code
   * $constraints = array(
   *   'EntityType' => 'node',
   *   'Bundle' => 'article',
   * );
   * @endcode
   *
   * Note that the specified constraints must be compatible with the data type,
   * e.g. for data of type 'entity' the 'EntityType' and 'Bundle' constraints
   * may be specified.
   *
   * @see \Drupal\Core\Validation\ConstraintManager
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   A data definition.
   *
   * @return array
   *   Array of constraints, each being an instance of
   *   \Symfony\Component\Validator\Constraint.
   *
   * @todo: Having this as well as $definition->getConstraints() is confusing.
   */
  public function getConstraints(DataDefinitionInterface $definition) {
    $constraints = array();
    $validation_manager = $this->getValidationConstraintManager();

    $type_definition = $this->getDefinition($definition->getDataType());
    // Auto-generate a constraint for data types implementing a primitive
    // interface.
    if (is_subclass_of($type_definition['class'], '\Drupal\Core\TypedData\PrimitiveInterface')) {
      $constraints[] = $validation_manager->create('PrimitiveType', array());
    }
    // Add in constraints specified by the data type.
    if (isset($type_definition['constraints'])) {
      foreach ($type_definition['constraints'] as $name => $options) {
        // Annotations do not support empty arrays.
        if ($options === TRUE) {
          $options = array();
        }
        $constraints[] = $validation_manager->create($name, $options);
      }
    }
    // Add any constraints specified as part of the data definition.
    $defined_constraints = $definition->getConstraints();
    foreach ($defined_constraints as $name => $options) {
      $constraints[] = $validation_manager->create($name, $options);
    }
    // Add the NotNull constraint for required data.
    if ($definition->isRequired() && !isset($defined_constraints['NotNull'])) {
      $constraints[] = $validation_manager->create('NotNull', array());
    }

    // If the definition does not provide a class use the class from the type
    // definition for performing interface checks.
    $class = $definition->getClass();
    if (!$class) {
      $class = $type_definition['class'];
    }
    // Check if the class provides allowed values.
    if (is_subclass_of($class,'Drupal\Core\TypedData\AllowedValuesInterface')) {
      $constraints[] = $validation_manager->create('AllowedValues', array());
    }

    return $constraints;
  }
}
