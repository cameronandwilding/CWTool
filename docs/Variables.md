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

