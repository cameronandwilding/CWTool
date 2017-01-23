Functional Utility
==================

Various functional style helpers. Each can be used individually. For particular helper please [check the tests](../tests/phpunit/Util/FunctionalTest.php).

Few examples
------------

# Memoize

Let's say you need to quickly compile a report in case something bad happens. This will be needed zero or many times - hence it is only needed to be called if at least once needed.

```php
$errors = new ErrorCollector();

$errorReporter = Funcitonal::memoize(function () use ($errors) {
  // Do some time consuming calculation with `$errors`.
  return $report;
});

// Do something.
if ($isErrorA) {
  print($errorReporter());
}

// Do another something.
if ($isErrorB) {
  print($errorReporter());
}

// etc.
```


# Any and All

Any (or all) verifies if there is at least one (or all) element that has a certain feature.

Say, you wonder there is an unpublished node in a result list:

```php
$hasUnpublished = Functional::any($nodes, function ($node) { return !$node->published; });
```


# Self callback

Lets say you have a custom form where you have a select field with last 10 node titles.

```php
$titles = array_map(Functional::selfCallFn('getTitle'), $nodeControllerList);
```


# Curry

Let's say you have an html wrapper function that wraps input into html tags:

```php
function wrap($tag, $text) {
  return "<$tag>$text</$tag>";
}
```

And you would like to wrap a list of node titles into H2 tags with array map:

```php
$htmlTitles = array_map(Functional::curry('wrap', 'h2'), $titles);
```


# Dot

Let's say you have a function that accepts a formatter callback that sanitize strings, and the formatter needs to contain multiple string formatters:

```php
function format_text($formatter, $text) {
  return $formatter($text);
}

$sanitized = formmat_text(Functional::dot('trim', 'check_plain', 'remove_forbidden_words'), $text);
```
