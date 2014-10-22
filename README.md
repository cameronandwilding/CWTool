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


Entity/node model containers
----------------------------

This module contains model containers for nodes. Class ```CWToolEntityWrapper``` for entities and ```CWToolNodeWrapper``` is specifically for nodes. Extend it for other entity types or bundles.

This model container in this form is a convenience hack to create content type model/controllers. Use them carefully and consider building clear models and controllers and application logic separation.
