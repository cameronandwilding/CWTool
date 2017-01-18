Dependency injection layer
==========================


The "why" is well explained on the Wikipedia pages of [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection).

CWTool is using [Pimple](https://github.com/silexphp/Pimple/tree/1.1) as service container for dependency injection. Read the documentation for more information about Pimple. 

In order to pick up services defined by other modules there is a hook to define:

```php
function hook_cw_tool_service_container_definition_alter(Pimple\Container $container) {
  $container['my.service'] = function (Pimple\Container $c) {
    return new MyServiceClass($c['another.service'], <ARG2>, ...);
  };
}
```

Check `cw_tool_get_container()` from `cw_tool.module` to get familiar with the available services. There are some that can be used without further specialization, such as the cron timer or the logger object.

Sometimes it is needed to override an existing service. That can be done easily in the hook implementation too by redefining the default one (usually in CWTool). Let's say the application requires a new logging facility - once the new class is ready, it can be hooked in:

```php
function my_module_cw_tool_service_container_definition_alter(Pimple\Container $container) {
  $container[CWTOOL_SERVICE_LOGGER] = function (Pimple\Container $c) {
    return new My\SpecialLogger();
  };
}
```

A generic application will likely have the following services:

- entity controller factory services (for each entity type and bundle)
- special logger service
- abstract factory services
- lock services
