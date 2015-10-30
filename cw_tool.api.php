<?php
/**
 * @file
 *
 * API.
 */

/**
 * Chance to add service.yml file folders to include to main page load.
 * The yml file name expected to be: services.yml
 *
 * @param \Pimple\Container $container
 */
function hook_cw_tool_service_container_definition_alter(Pimple\Container $container) {
  $container['my-service'] = function (Pimple\Container $c) {
    return new MyServiceClass($c[OTHER_SERVICE]);
  };
}

/**
 * Collects application variables.
 *
 * @param \CW\Manager\VariableManager $variableManager
 */
function hook_cw_tool_app_variables(\CW\Manager\VariableManager $variableManager) {
  $variableManager->addVariable(new \CW\Params\Variable('myVar', 'My variable'));

  $varGroup = new \CW\Params\VariableGroup(t('My group'));
  $varGroup->addVariable(new \CW\Params\Variable('myOtherVar', 'My other var'));
  $variableManager->addGroup($varGroup);
}

/**
 * Allows defining field -> entity controller factory mapping that can be used
 * when grabbing node/entity reference field values.
 *
 * @param \CW\Params\HookFieldControllerReferenceMapCollector $collector
 */
function hook_cw_tool_field_controller_reference_map(\CW\Params\HookFieldControllerReferenceMapCollector $collector) {
  $collector->add('field_some_entity_reference', MY_NODE_BUNDLE_FACTORY);
  $collector->add('field_some_node_reference', MY_NODE_BUNDLE_FACTORY, \CW\Util\FieldUtil::KEY_NODEREFERENCE_ID);
}
