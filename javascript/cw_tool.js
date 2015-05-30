/**
 * @file
 *
 * Generic tools.
 */

var CWTool = (function () {

  'use strict';

  var self = {};

  /**
   * Verify if all sub properties exist of an object.
   *
   * @example
   * For example to verify if window.class.foo.bar.x exist:
   * propertyChainExist(window, ['class', 'foo', 'bar', 'x']);
   *
   * @param object
   *  Start object.
   * @param properties
   *  Array of string property names.
   * @returns {boolean}
   */
  self.propertyChainExist = function ( object, properties ) {
    var current_object = object;

    var property;
    while (property = properties.shift()) {
      if (!current_object.hasOwnProperty(property)) {
        return false;
      }
      current_object = current_object[property];
    }

    return true;
  };

  /**
   * Get a property value on an object - and returns a false
   *
   * @param object
   * @param properties
   * @returns {*}
   */
  self.propertySafeGet = function ( object, properties ) {
    var current_object = object;

    var property;
    while (property = properties.shift()) {
      if (!current_object.hasOwnProperty(property)) {
        return undefined;
      }
      current_object = current_object[property];
    }

    return current_object;
  };

  /**
   * Compose an URL query string.
   * Eg.: {a: foo, b: 1} -> a=foo&b=1
   *
   * @param query
   *  Keyed dictionary.
   * @returns {string}
   */
  self.createURLQueryString = function ( query ) {
    var items = [];
    for (var key in query) {
      if (!query.hasOwnProperty(key)) {
        continue;
      }

      items.push(encodeURIComponent(key) + '=' + encodeURIComponent(query[key]));
    }

    return items.join('&');
  };

  return self;

})();
