CW Tool
=======


Install
-------

* copy the module into the modules folder
    * even better if you add it as a git submodule
    * even better through composer: ```composer require cw/tool:dev-8.x-1.0```
* install composer dependencies:
    * ```composer install``` in the module folder
* enable cw_tool module
    * ```drush en cw_tool```


# Main features


Entity Controllers and Factories
--------------------------------

Entities in code should be handled through entity controller classes. There should be a separate class for each entity bundle in order to separate business logic.

Entities are created through dedicated entity controller factories - which are all services.
 
*Sample: Article bundle controller*

Create an entity controller factory service in your module's \*.services.yml:

```YML
services:
  my_module.entity_factory.node.article:
    class: Drupal\cw_tool\Factory\EntityControllerFactory
    arguments:
      - @entity.manager
      - 'node'
      - 'Drupal\my_module\Controller\ArticleController'
```

Then you can call: 

    \Drupal::service('my_module.entity_factory.node.article')->initWithID(123);

The controller classes are subclass of *Drupal\cw_tool\Controller\AbstractEntityController*:

```PHP
namespace Drupal\my_module\Controller;
use Drupal\cw_tool\Controller\AbstractEntityController;
class ArticleController extends AbstractEntityController { }
```

Entity Creator Factories
------------------------

Entities can be created through the entity creator class. There should be a separate param class for the entity creation parameters.

There should be a separate service per entity type where the argument is the Drupal entity class.

*Sample: Node creator*

Create an entity creator factory in your module's \*.services.yml:

```YML
services:
  my_module.entity_creator.node:
    class: Drupal\cw_tool\Factory\EntityCreator
    arguments:
      - 'Drupal\node\Entity\Node'
```

Then you can call:

    \Drupal::service('my_module.entity_creator.node')->create($node_params);

The node params should be a class that implements *Drupal\cw_tool\Params\EntityCreationParams* to provide the required values as per the entity type being created.


Template Processors
-------------------

@Todo - complete
