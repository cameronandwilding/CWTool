Array Utility
=============

Various array related helpers. Each can be used individually. For particular helper please [check the tests](../tests/phpunit/Util/ArrayUtilTest.php).


Examples
--------


# Map translate

Map translate is useful to replace obvious if/switch structures. Let's say you design a controller factory dispatcher:

```php
function get_node_controller_factory($bundle) {
  return ArrayUtil::mapTranslate([
    'article' => My\Controller\Article::class,
    'blog' => My\Controller\Blog::class,
    'cover' => My\Controller\Cover::class,
  ], $bundle, CW\Controller\NodeController::class);
}
```


# Merging collections

Merging collections can be useful when you need to merge more than 2 (or even just 2) arrays. One example is if you define Theme classes for your hook_theme:

```php
function my_module_theme() {
  $theme_info = [];
  ArrayUtil::mergeCollection($theme_info, [
    MyRegisterFormThemeClass::getHookThemeArray(),
    MySidebarThemeClass::getHookThemeArray(),
    MyBlockThemeClass::getHookThemeArray(),
  ]);
  return $theme_info;
}
```


# Transform with keys

This function comes handy when you need to do array_map however you want the keys also change. For example when you need a select form element option list using node id-title pairs from a list of nodes:

```php
$fnKeyAndTitle = function ($node) { return [$node->nid, $node->title]; };
$form['nodes']['#options'] = ArrayUtil::transformWithKeys($nodes, $fnKeyAndTitle);
```
