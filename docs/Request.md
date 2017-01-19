Page request
============

`\CW\Util\Request` is a base class to provide information about page requests. To get the current one you call:

```php
$req = \CW\Util\Request::current();
```

You can ask for the request time, GET, POST and Drupal parameters.

It is recommended to make it a service and subclass it to your application in order to have application related functions on it. A few sensible question from an app request object could be:

- `isAjaxRequest()`
- `getPurchasePromoCodeFromRequest()`
- `getSourceIP()`

