Custom blocks
=============

CWTool offers the abstract `Block` class to use for defining the various parts of a block (hook) in one place.

# Example

To start you need to subclass `Block` and add the minimum configuration necessary:

```php
class MyBlock extends Block {
  const DELTA = 'myblock';

  public static function getDelta() {
    return self::DELTA;
  }

  public static function getInfo() {
    return [
      'info' => t('My super block'),
    ];
  }

  function getRenderArray() {
    return [
      'subject' => t('My block'),
      'content' => 'It works',
    ];
  }
}
```

And second this class needs to be connected in the Drupal block hooks:

```php
function core_block_info() {
  $info = [];
  \CW\Util\ArrayUtil::mergeCollection($info, [
    // ...
    MyBlock::getHookBlockInfoArray(),
  ]);
  return $info;
}

function mymodule_block_view($delta = '') {
  switch ($delta) {
    // ...
    
    case MyBlock::DELTA:
      return MyBlock::getHookBlockViewArray();
      
    default:
      return [];
  }
}
```
