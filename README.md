CW Tool
=======

Install
-------

* copy the module into the modules folder
    * even better if you add it as a git submodule
* update composer dependencies:
    * ```composer update``` in the module folder
* enable cw_tool module
    * ```drush en cw_tool```


Main features
-------------

* dependency injection layer
* drupal variable adapter
* entity controllers and factories
* entity creators
* site variable and its form handlers
* drupal object handler (entity crud) adapter
* generic model interface
* utilities
    * array util
    * cron timer
    * date util
    * entity batch saver
    * field util
    * form util
    * link abstraction
    * identity map
    * request object
    * list data type


Dependency injection layer
--------------------------

CWTool is using Symfony's service container for dependency injection. It reads the defined yaml files to collect the services and make them enable for the service container. For a service yaml file example look at the cw_tool/config/services.yml file.

In order to pick up services defined by other modules there is a hook to define:

```php
function hook_cw_tool_service_container_definition_alter(\CW\Util\SimpleList $collection) {
  $collection->add('my/custom/path');
}
```

Check the services.yml file in cw_tool in order to get familiar with the available services. There are some that can be used without further specialization, such as the cron timer or the logger object.


Entity controllers and factories
--------------------------------

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

Entity controllers are created through factories in order to provide common functionalities, such as caching. The entity controller factory is usually defined in the service container, such as this for nodes:

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


Entity creators
---------------

Entity creators are handy when creating new specialized entities, sort of like a content factory. There are already creators for node and user entity types and can be created more (by implementing the Creator interface) if necessary.

Example:

```php
$articleParams = new NodeCreationParams('article', 'Main title');
$articleParams->setField('field_subtitle', 'My subtitle');
$articleParams->setProperty('status', NODE_NOT_PUBLISHED);

$nodeFactory = cw_tool_get_container()->get('my-node-controller-factory');
$nodeController = $nodeFactory->initNew(new NodeCreator($articleParams));
```


General development guidelines
------------------------------

**Use entities via their controllers**

Entities should have their own controller, and loaded with the controller factory.

**Controller should contain the minimum necessary behavior**

Controllers suppose to contain data access and minimum business logic. Controller should not generate themed output. Controller should not sanitize it's content.

Controller should hold the field names (as constants), property or state constants.

For extra behavior (such as controller rendered output, forms, ets) there should be a dedicated class implementing ControllerAware or ControllerContainer.

**Services over static classes**

When new class needed to wrap a functionality, a new service is preferred. Usually they contain a logger.


Helper functions
----------------

Helper tools for generic Drupal7 development (simple functions in the includes):

* update hook helpers
    * menu related updates
    * Field API crud
    * features
* field collection helpers
* taxonomy
* etc
