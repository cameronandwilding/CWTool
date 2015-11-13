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


CWTool is using [Pimple](https://github.com/silexphp/Pimple/tree/1.1) as service container for dependency injection. Read the documentation for more information about Pimple. 

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


Object (and entity) creator configurations and executors
--------------------------------------------------------

Creator executors are a complete configurable workflow to create a set of defined objects (including entities) described by configuration. It is also an extensible facility to create specific object creators for specific types and purposes. The configuration can be injected from configuration files or simply from code. It's using the same creator mechanism explained in the previous section.

Execution using a Yaml source file:

```php
$confReader = new \CW\Util\YamlConfigurationReader('~/Desktop/test_content.yaml');
$utilities = \CW\Util\BasicUtilityCollection::createInstance();

$executionManager = new \CW\Manager\CreatorConfigurationExecutorManager($confReader, $utilities);

$result = $executionManager->generate();

var_dump($result['my_second_article']);
```

Example configuration file in Yaml format:

```
items:
  user_one:
    executor: CW\Factory\EntityCreatorConfigurationExecutor
    param:
      class: CW\Params\UserCreationParams
      args:
        - %randomString(32)
        -
          3: administrator
    creator:
      class: CW\Factory\UserCreator
      args: [@param]
    properties:
      mail: %randomString
      pass: cakes
  node_one:
    executor: CW\Factory\EntityCreatorConfigurationExecutor
    param:
      class: CW\Params\NodeCreationParams
      args: ['article']
    creator:
      class: CW\Factory\NodeCreator
      args: [@param]
  node_two:
    executor: CW\Factory\EntityCreatorConfigurationExecutor
    param:
      class: CW\Params\NodeCreationParams
      args: ['article', "Test node title", "und", $user_one.uid]
    creator:
      class: CW\Factory\NodeCreator
      args: [@param]
    properties:
      status: 0
    fields:
      body:
        value: "Sample body <strong>content</strong>."
        format: full_html
      field_subtitle: "Example simple text"
      field_parent_article:
        target_id: $node_one.nid
      field_cover_image:
        uri: public://cover.jpeg
```



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


Variable manager
----------------


Application variables are managed with the variable manager. Hook can be implemented to collect:

```php
function hook_cw_tool_app_variables(\CW\Manager\VariableManager $variableManager) {
  $variableManager->addVariable(new \CW\Params\Variable('myVar', 'My variable'));

  $varGroup = new \CW\Params\VariableGroup(t('My group'));
  $varGroup->addVariable(new \CW\Params\Variable('myOtherVar', 'My other var'));
  $variableManager->addGroup($varGroup);
}
```

All application variables should be added in this hook in order to have their presence on the admin UI.


Forms
-----


Alters for existing forms (not defined by the module) should be registered in ```hook_form_NAME_alter()``` and a static class method (```class::alter```) should add the necessary alterations.

Adding new submit or validation callbacks via CW/Util/FormUtil:

```php
class SomeExistingForm {
	public static function alter(&$form, &$form_state) {
		FormUtil::registerSubmitCallback($form, [__CLASS__, 'submit']);
	}
	
	public static function submit(&$form, &$form_state) { }
}
```

For custom forms subclass ```CW\Form\FormBuilder```:

```php
class MyForm extends CW\Form\FormBuilder {
	public static function build($form, $form_state) {
		$form['submit'] = ['#type' => 'submit', 'value' => t('Submit')];
		return $form;
	}
	
	public static function submit(&$form, &$form_state) {
		// Do some action.
	}
}

// Calling the form:
$form = MyForm::get();
$html = drupal_render($form);
```


Theme
-----


To create a new theme subclass ```CW\Theme\Theme```:

```php
class MyThemeClass extends Theme {
    public function __construct($requiredVars) {
        ...
    }

    public static function getName() {
        return 'my_theme_name';
    }
    
    protected static function getDefinition() {
        return array(
            'template' => 'templates/my-template',
            'variables' => array(
                'var1' => NULL,
                'var2' => NULL,
            ),
        );
    }
    
    public function getVariables() {
        return array(
            'var1' => ...,
            'var2' => ...,
        );
    }
}
```

Then add it to hook_theme():

```php
function my_module_theme() {
  $theme_info = [];
  ArrayUtil::mergeCollection($theme_info, [
    MyThemeClass::getHookThemeArray(),
    MyOtherThemeClass::getHookThemeArray(),
  ]);
  return $theme_info;
}
```

Using the theme:

```php
$myTheme = new MyThemeClass($requiredVars);
$out = $myTheme->render();
```


Template preprocessors
----------------------


It is recommended to subclass ```AbstractThemeProcessor``` for all separable (pre)processing tasks. The subclass is responsible for deciding if it's applicable (eg.: ArticlePreprocessor should be only for article node variable preprocessing).
 
 
```php
class MyArticleNodePreprocessor extends CW\Processor\AbstractNodeProcessor {
    const VAR_TITLE = 'my_title';
    public function execute() {
        $this->setVar(self::VAR_TITLE, 'foobar');
    }
    public function isApplicable() {
        return $this->getVar('node')->type == MyArticleController::BUNDLE;
    }
}

function my_theme_node_preprocess(&$vars) {
    MyArticleNodePreprocessor::process($vars);
    MyBlogNodePreprocessor::process($vars);
}
```



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
