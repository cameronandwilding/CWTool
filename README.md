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
 
**Sample: Article bundle controller**

Create an entity controller factory service in your module's *.services.yml:

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

The controller classes are subclass of **Drupal\cw_tool\Controller\AbstractEntityController**:

```PHP
namespace Drupal\my_module\Controller;
use Drupal\cw_tool\Controller\AbstractEntityController;
class ArticleController extends AbstractEntityController { }
```


Template Processors
-------------------

@Todo - complete
