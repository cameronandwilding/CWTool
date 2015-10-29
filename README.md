CW Tool
=======

[![Build Status](https://travis-ci.org/cameronandwilding/CWTool.png?branch=v3)](https://travis-ci.org/cameronandwilding/CWTool)

Install
-------


* copy the module into the modules folder
    * even better if you add it as a git submodule
    * even better through composer: ```composer require cw/tool```
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


CWTool is using (Pimple)[https://github.com/silexphp/Pimple/tree/1.1] as service container for dependency injection. Read the documentation for more information about Pimple. 

In order to pick up services defined by other modules there is a hook to define:

```php
function hook_cw_tool_service_container_definition_alter(Pimple\Container $container) {
  $container['my.service'] = function (Pimple\Container $c) {
    return new MyServiceClass($c['another.service'], 'fixed_param');
  };
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

**Warning**: always load controllers with their dedicated factory, because the cache will save the first load. (Eg: don't load articles with the generic node factory or blog node factory.)


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


Variable manager
----------------


Application variables are managed with the variable manager. Hook can be implemented to collect:

```php
function hook_cw_tool_app_variables(\CW\Manager\VariableManager $variableManager) {
  $variableManager->addVariable(new \CW\Params\Variable('myVar', 'My variable'));
}
```

All application variables should be added in this hook in order to have their presence on the admin UI.


Drush commands
--------------

**Entity controller class scaffolding**

Creates boilerplate PHP class code for bootstrapping.

```bash
drush cwt-sc-ctrl node blog --namespace=My\Corp
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

When new class needed to wrap a functionality, a new service is preferred. Usually they contain a logger at least.

**Param objects over arrays**

Avoid using arrays as argument. Make a parameter object instead.


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


Documentation
-------------

Execute Doxygen generator:

```doxygen Doxygen```

The Doxygen binary is a requirement.


Questions
=========
* Improve theme function (theme class no array keys)
* Form - static instance or service, preserve for alter / validate / build
