Entity creators
===============

# Scenario 1

You have to create either one complex content or a collection of contents automatically - for example on a book site a new book will have template chapters added on creation - or a purchase on an ecommerce site would create a few reports and recipes.

Normally this is done via creating some node objects, filling out the fields and properties and making sure that it stays consistent and following the business logic.

The issue is that this is using low level Drupal API, the code need to touch data arrays with nested levels. Other than that the code needs to mimic the business rules (in case fields require special attention).

# Scenario 2

During development or testing it might be necessary to create a (large) collection of test content. Making that dynamic and satisfying is a lot of work with risky code - as explained in the previous scenario.

# Solution

Entity creators are for quick and organized content creation using the CWTool entity controller API.

# Example

Creating an article type content with a little setting:

```php
$articleParams = new NodeCreationParams('article', 'Main title');
$articleParams->setField('field_subtitle', 'My subtitle');
$articleParams->setProperty('status', NODE_NOT_PUBLISHED);

$nodeFactory = cw_tool_get_container()->get('my-node-controller-factory');
$nodeController = $nodeFactory->initNew(new NodeCreator($articleParams));
```

You have the chance to subclass either NodeCreationParams or the NodeCreator to satisfy special needs. Subclass NodeCreationParams if the specialties are as simple as putting data into the fields and properties. Subclass the NodeCreator if your specs are more than just data - for example a new content needs to be registered in an API or change some other application setting.


# Scenario 3

You need to create even more content - or the created collection and configuration needs to be dynamic (for example a capability to have the recipe in form of an uploaded config file).

# Solution

The entity (actually it's a more general object creator) configuration API is able to generate content from config files, such as YAML (the one currently implemented).

The configuration files can contain any number of content - each described to the smallest details: properties and fields. All content in a config is tagged and reusable in other content - let's say you need a book content with an author reference you can create the author first and use its id in the book content's reference field.

The config builder can apply one utility class that is used by the config file to have dynamic data in the generated content. Let's say you create a book content, however each book content will need a special ISBN number generated from the current time and taxonomy - the utility class can expose a static method to provide this value. These methods expect zero, one or more arguments, so they can be reused through various config files.

Object (and entity) creator configurations and executors
--------------------------------------------------------

# Example

Very simple config with one user and two content description:

```php
$confReader = new \CW\Util\YamlConfigurationReader(__DIR__ . '/assets/test_content.yaml');
$utilities = \CW\Util\BasicUtilityCollection::createInstance();

$executionManager = new \CW\Manager\CreatorConfigurationExecutorManager($confReader, $utilities);

$result = $executionManager->generate();

var_dump($result['node_two']);
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
      args: ["@param"]
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
      args: ["@param"]
      
  node_two:
    executor: CW\Factory\EntityCreatorConfigurationExecutor
    param:
      class: CW\Params\NodeCreationParams
      args: ['article', "Test node title", "und", $user_one.uid]
    creator:
      class: CW\Factory\NodeCreator
      args: ["@param"]
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

