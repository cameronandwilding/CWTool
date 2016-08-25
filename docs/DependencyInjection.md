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
