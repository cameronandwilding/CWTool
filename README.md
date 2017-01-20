CW Tool
=======

- Drupal 7: [![Build Status](https://travis-ci.org/cameronandwilding/CWTool.png?branch=v3.1)](https://travis-ci.org/cameronandwilding/CWTool)
- Drupal 8: [![Build Status](https://travis-ci.org/cameronandwilding/CWTool.png?branch=8.x-1.0)](https://travis-ci.org/cameronandwilding/CWTool)


# What does it do?

CWTool has 2 main purpose. First it is a structuring framework over the Drupal API. Second it is a helper library for PHP and Drupal.

Why Drupal needs a structuring framework? Drupal (especially the 7th version) uses associated arrays everywhere, and even the more object oriented parts of it is mostly a bunch of loosely coupled weak objects. As an example nodes are plain PHP objects containing numerous associated arrays. Work with node objects are dangerous and not sustainable. Wrapping them into controllers and models however gives you well defined business layer and safety.
 
Another example is forms. In a old fashioned Drupal project forms are defined and altered in hooks and plain functions without much organization. Form classes and form extenders can help creating cohesive classes and reducing redundant code to the minimum.

Other than providing classes for building up a business domain CWTool intend to provide the Drupal API through adapters to increase testability of the business layer. For example entity API and variable handling is via adaptors, which means the Drupal implementation is just one version and you can override it anytime.

And possibly a +1 purpose of CWTool is to provide dependency injection to the application via a service container. Using services from DI allows the app (and developers) to switch and replace services (such as logging, variable handling, object loading, caching - to name a few) without much hassle.

The utility part of CWTool is a pure helper function library to speed up development time by reducing redundant and error prone code. There are various utilities for strings, arrays, dates, functional style code, etc. 


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
* [Blocks](docs/Block.md)
* Structural aid:
    * [Self factory](src/CW/Factory/SelfFactory.php)
* Utilities:
    * [Arrays](docs/ArrayUtil.md)
    * [Functionals](docs/FunctionalUtil.md)
    * [Page request](docs/Request.md)
    

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
