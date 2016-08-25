Entity controllers and factories
================================


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
│ Controller factory   ├───┼───▶ IdentityMap
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

Entity controllers are created through factories in order to provide common functionality, such as caching. The entity controller factory is usually defined in the service container, such as this for nodes:

```
  cw.node-controller.factory:
    class: CW\Factory\EntityControllerFactory
    arguments:
      - @cw.identity-map
      - @cw.object-handler.drupal
      - 'CW\Controller\NodeController'
      - 'node'
      - @cw.logger
```

An entity controller factory must define the controller class and the entity type. Accessing the factory is through the service container:

```php
cw_tool_get_container->get('my-controller-factory');
```

And then accessing the concrete entity is either passing the ID or the whole object:

```php
$userController = cw_tool_get_container->get('my-user-controller-factory')->initWithId(123);
$nodeController = cw_tool_get_container->get('my-node-controller-factory')->initWithEntity($node);
```

**Warning**: always load controllers with their dedicated factory, because the cache will save the first load. (Eg: don't load articles with the generic node factory or blog node factory.)


Entity fields
-------------


Entity controllers (```AbstractEntityController```) and entity form values (```NodeFormState```) are implementing ```FieldAccessor```. Use it to get/set field values or referenced entities. Common field values are defaulted into function args or included in ```FieldUtil```.

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


Special reference loaders
-------------------------

In Drupal there are fields that has additional metadata. An example is the image field, where the default data is a file ID, however you often find image dimension, extension and other info on the node object. This metadata cannot always be obtained through the basic reference getter, such as here:
 
 
```php
$article = cw_tool_get_container->get('my-article-factory')->initWithId(123);
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