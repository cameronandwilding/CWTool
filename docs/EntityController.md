Entity controllers and factories
================================


# Scenario

One of the most common operation in a Drupal application is to manipulate node or user objects:

'''php
$node = node_load(123);
$node->field_second_cover_image[LANGUAGE_NONE][0]['uri'] = $newUri;
node_save($node);
'''

This pattern is prone to errors and easy to make an unmaintainable (mostly redundant) pile of code with it. A better solution is to use controllers for entities - one controller representing one entity bundle. The CWTool entity controller not only allows controllers to collect all application knowledge in it, but it does provide a conventient way to manipulate the entity itself.

Before we see an example a quick word on how controllers are accessed in code. Controllers have a few features that helps their job around entities (such as caching, logging, etc). In order not to bother with it each time you instantiate a controller they are always provided by a controller factory. These factories are services and being one they need to be defined in the service container. The following diagram shows that the service container provides the controller factory which provides the controller:

```
┌──────────────────────┐
│                      │
│ Service container    │
│                      │
└──────────────────────┘
            │
            │
            ▼
┌──────────────────────┐   ┌───▶ Logger
│                      │   ├───▶ Controller
│ Controller factory   ├───┼───▶ Identity map
│                      │   └───▶ Entity type information
└──────────────────────┘
            │
            │
            ▼
┌──────────────────────┐
│                      │                       ID: #
│ Concrete controller  │─────▶ initWithID(#) : TYPE: #
│                      │                       Entity: @{...}
└──────────────────────┘
```


# Example

To have an entity controller you need first the controller class:

```php
// somewhere in mymodule/src/Controller/Node/MyNode.php

class MyNode extends CW\Controller\NodeController {

  // Class node bundle.
  const BUNDLE = 'mynode';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityBundle() {
    return self::BUNDLE;
  }

}
```

Then define the factory for this controller for the service container:

```php
function mymodule_cw_tool_service_container_definition_alter(Pimple\Container $container) {
  $container['my-node-factory'] = function (Container $c) {
    return new EntityControllerFactory(
      $c[CWTOOL_SERVICE_IDENTITY_MAP],
      $c[CWTOOL_SERVICE_OBJECT_HANDLER],
      'My\Controller\Node\MyNode',
      'node',
      $c[CWTOOL_SERVICE_LOGGER]
    );
  };
}
```

An entity controller factory must define the controller class and the entity type. Accessing the factory is through the service container:

```php
cw_tool_get_container()['my-controller-factory'];

// Or even better to create a quick accessor:

/**
 * \CW\Factory\EntityControllerFactory
 */
function mymodule_mynode_factory() {
  return cw_tool_get_container()['my-controller-factory'];
}
```

And then accessing the concrete entity is either passing the ID or the whole object:

```php
$userController = cw_tool_get_container()['my-user-controller-factory']->initWithId(123);
// or simpler:
$nodeController = mymodule_mynode_factory()->initWithEntity($node);
```

**Warning**: always load controllers with their dedicated factory, because the cache will save the first load. (Eg: don't load articles with the generic node factory or blog node factory.)

A good practice to have an abstraction for getting all the application related entity controllers from one place that can decide the appropriate controller factory and do error handling if needed.


Entity fields
-------------

Entity controllers (```AbstractEntityController```) and entity form values (```NodeFormState```) are implementing ```FieldAccessor```. Use it to get/set field values or referenced entities. Common field values are defaulted into function args or included in ```FieldUtil```.


Mapping reference fields to factories
-------------------------------------

To define types of references, use hook ```cw_tool_field_controller_reference_map``` to match them with their associated entity factory:

```php
function hook_cw_tool_field_controller_reference_map(\CW\Params\HookFieldControllerReferenceMapCollector $collector) {
  $collector->add('field_some_entity_reference', MY_NODE_BUNDLE_FACTORY);
  $collector->add('field_some_node_reference', MY_NODE_BUNDLE_FACTORY, \CW\Util\FieldUtil::KEY_NODEREFERENCE_ID);
}
```

And then you can just call the entity ref getter on the entity controller:

```php
$ctrl = cw_tool_get_container()[MY_CONTROLLER_FACTORY_SERVICE];
$tagControllers = $ctrl->fieldReferencedEntityControllersLookup(MyController::FIELD_TAG);
```

Not that at this point we don't need to use the controller factory or define the field key that holds the entity id.


Special reference loaders
-------------------------

In Drupal there are fields that has additional metadata. An example is the image field, where the default data is a file ID, however you often find image dimension, extension and other info on the node object. This metadata cannot always be obtained through the basic reference getter, such as here:
 
 
```php
$article = cw_tool_get_container()['my-article-factory']->initWithId(123);
$imageController = $article->fieldReferencedEntityController('<fieldname>', <image factory>);
```

Generally or for special entities the responsibility of transferring the special properties (from the node object) is on the concrete controller class - via implementing the `attachExtraReferencedControllerPropertiesFromParentController` function. See this example from the ImageController class:

```php
class ImageController extends FileController {

    ...
  
    protected function attachExtraReferencedControllerPropertiesFromParentController(array $fieldItem) {
      parent::attachExtraReferencedControllerPropertiesFromParentController($fieldItem);
  
      $this->setAltFromHostField(@$fieldItem['alt']);
      $this->setTitleFromHostField(@$fieldItem['title']);
      $this->setWidthFromHostField(@$fieldItem['width']);
      $this->setHeightFromHostField(@$fieldItem['height']);
    }
    
    ...

}
```