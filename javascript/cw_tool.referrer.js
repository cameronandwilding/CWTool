/**
 * @file
 *  Add referrer related behaviours.
 */
var CW = CW || {};

(function($) {

  'use strict';

  CW.Referrer = $.extend({}, CW.Path, {

    uri : document.referrer

  });

}(jQuery));