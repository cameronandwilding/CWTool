CW Tool
=======

Install
-------

- copy the module into the modules folder
 - even better if you add it as a git submodule
- update composer dependencies:
 - ```composer update``` in the module folder
- enable cw_tool module
 - ```drush en cw_tool```


Main features
-------------

- dependency injection layer
- drupal variable adapter
- entity controllers and factories
- entity creators
- site variable and its form handlers
- drupal object handler (entity crud) adaptor
- generic model interface
- utilities
 - array util
 - cron timer
 - date util
 - entity batch saver
 - field util
 - form util
 - link abstraction
 - identity map
 - request object
 - list data type


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



Helper functions
----------------

Helper tools for generic Drupal7 development (simple functions in the includes):

- update hook helpers
 - menu related updates
 - Field API crud
 - features
- field collection helpers
- taxonomy
- etc
