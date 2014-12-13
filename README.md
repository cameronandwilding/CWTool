CW Tool
=======

Helper tools for generic Drupal7 development:

- update hook helpers
 - menu related updates
 - Field API crud
 - features
- field collection helpers
- taxonomy
- etc


Entity model
------------

Entity model is an object abstraction layer over the Drupal entity objects. It's purpose is to give a place for model properties, such as fields or computed values. Generally speaking - if it's something that has no action in it.
 
Entity controller
-----------------

Entity controller is the main handler of entities (including nodes). Controller has a model where the controller gather it's data from. Controllers are instantiated through controller factories - using the dependency injection layer. See the following example.

Let's say the app has an node type - article, which you would like to create a controller for. First you subclass the basic entity controller:

```php

```
