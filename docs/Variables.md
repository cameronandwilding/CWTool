Variable manager
================

Almost always an application has many configuration on the application level. The CWTool variable manager API offers an easy interface to define variables - which then presented on the admin at path: `admin/config/application/variables`.

Variable definitions are collection via the hook `hook_cw_tool_app_variables`:

```php
function mymodule_cw_tool_app_variables(\CW\Manager\VariableManager $variableManager) {
  $variableManager->addVariable(new \CW\Params\Variable('myVar', t('My variable')));

  $varGroup = new \CW\Params\VariableGroup(t('My group'));
  $varGroup->addVariable(new \CW\Params\Variable('myOtherVar', t('My other var')));
  $varGroup->addVariable(new \CW\Params\Variable('myAnotherVar', t('My another var')));
  $variableManager->addGroup($varGroup);
}
```

As you can see variables can be added on top levels or in groups.
