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

