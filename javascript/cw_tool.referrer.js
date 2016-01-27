/**
 * @file
 *  Add referrer related behaviours.
 */
var CW = CW || {};

(function () {

  'use strict';

  /**
   * @memberof CW
   * @property {object} Referrer - Handler to get handle referrer information from javascript.
   */
  CW.Referrer = jQuery.extend({}, CW.Path, {

    /** @inheritdoc */
    uri : document.referrer

  });

})();