Theming
=======


# Theme classes

Althoug Drupal's theme hook can collect the defined theme-abel elements it is still recommended to group the related elements together into theme classes. This is to make sure that hooks are not getting overbloated and cohesive logic is at one place.

To create a new theme subclass ```CW\Theme\Theme```:

```php
class MyThemeClass extends Theme {
    public function __construct($requiredVars) {
        ...
    }

    public static function getName() {
        return 'my_theme_name';
    }
    
    protected static function getDefinition() {
        return array(
            'template' => 'templates/my-template',
            'variables' => array(
                'var1' => NULL,
                'var2' => NULL,
            ),
        );
    }
    
    public function getVariables() {
        return array(
            'var1' => ...,
            'var2' => ...,
        );
    }
}
```

Then add it to hook_theme():

```php
function my_module_theme() {
  $theme_info = [];
  ArrayUtil::mergeCollection($theme_info, [
    MyThemeClass::getHookThemeArray(),
    MyOtherThemeClass::getHookThemeArray(),
  ]);
  return $theme_info;
}
```

Using the theme:

```php
$myTheme = new MyThemeClass($requiredVars);
$out = $myTheme->render();
```


# Template preprocessors

The default way to interact with template content is to define template hooks and there manipulate the variables array. CWTool offers Processor classes to separate code for these processors.

It is recommended to subclass ```AbstractThemeProcessor``` for all separable (pre)processing tasks. In case of nodes CWTool has already a dedicated class: `AbstractNodeProcessor`. The subclass is responsible for deciding if it's applicable (eg.: ArticlePreprocessor should be only for article node variable preprocessing).
 
 
```php
class MyArticleNodePreprocessor extends CW\Processor\AbstractNodeProcessor {
    const VAR_TITLE = 'my_title';
    public function execute() {
        $this->setVar(self::VAR_TITLE, 'foobar');
    }
    public function isApplicable() {
        return $this->getVar('node')->type == MyArticleController::BUNDLE;
    }
}
```

And the way it can be referred from the processor hooks:

```php
function my_theme_node_preprocess(&$vars) {
    MyArticleNodePreprocessor::process($vars);
    MyBlogNodePreprocessor::process($vars);
}
```
