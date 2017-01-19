CW Tool
=======

- Drupal 7: [![Build Status](https://travis-ci.org/cameronandwilding/CWTool.png?branch=v3.1)](https://travis-ci.org/cameronandwilding/CWTool)
- Drupal 8: [![Build Status](https://travis-ci.org/cameronandwilding/CWTool.png?branch=8.x-1.0)](https://travis-ci.org/cameronandwilding/CWTool)

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


* [Dependency injection layer](docs/DependencyInjection.md)
* [Entity controllers and factories](docs/EntityController.md)
* [Entity creators](docs/Creators.md)
* [Site variable and its form handlers](docs/Variables.md)
* [Forms](docs/Forms.md)
* [Theme and template management](docs/Theme.md)
* [Generic model interface](docs/Model.md)
* Utilities:
    * [Arrays](docs/ArrayUtil.md)
    

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
