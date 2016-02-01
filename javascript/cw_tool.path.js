;/**
 * @file
 *
 * Add path related behaviours.
 */

var CW = CW || {};

/**
 * @memberof CW
 * @property {object} Path - Handler to get handle path information from javascript.
 */
CW.Path = (function () {

  'use strict';

  /**
   * If the path components have been _initialized or not.
   * @type {bool}
   */
  var initialized = false;

  /**
   * Stores the uri to be used.
   * @type {string}
   */
  var uri = document.location.href;

  /**
   * Procotol component.
   * @type {string}
   */
  var urlProtocol = '';

  /**
   * Hostname component.
   * @type {string}
   */
  var urlHostname = '';

  /**
   * Path component.
   * @type {string}
   */
  var urlPath = '';

  /**
   * Parameters component.
   * @type {string}
   */
  var urlSearch = '';

  /**
   * Parsed parameters component.
   * @type {Array}
   */
  var urlQueryParam = [];

  /**
   * Fragment component.
   * @type {string}
   */
  var urlFragment = '';

  /**
   * Initializes the components if necessary.
   */
  function initializeIfNecessary() {
    if (!initialized) {
      parseURI();
      initialized = true;
    }
  }

  var self = {

    /**
     * Gets the URI component.
     * @returns {string}
     */
    getURI: function() {
      return uri;
    },

    /**
     * Sets the URI component.
     * @param {string} _uri
     */
    setURI: function(_uri) {
      uri = _uri;
    },

    /**
     * Gets the Protocol component.
     * @returns {string}
     */
    getURLProtocol: function() {
      initializeIfNecessary();
      return urlProtocol;
    },

    /**
     * Sets the protocol component.
     * @param {string} val
     */
    setURLProtocol: function(val) {
      urlProtocol = val;
    },

    /**
     * Gets the hostname component.
     * @returns {string}
     */
    getURLHostname: function() {
      initializeIfNecessary();
      return urlHostname;
    },

    /**
     * Gets the base hostname component.
     * @returns {string}
     */
    getURLBaseHostname: function() {
      if (!this.isIPv4Address(this.getURLHostname())) {
        var components = this.getURLHostname().split('.');
        return components.slice(-2).join('.');
      }
      return this.getURLHostname();
    },

    /**
     * Sets the hostname component.
     * @param {string} val
     */
    setURLHostname: function(val) {
      urlHostname = val;
    },

    /**
     * Gets the path component.
     * @returns {string}
     */
    getURLPath: function() {
      initializeIfNecessary();
      return urlPath;
    },

    /**
     * Sets the path component.
     * @param {string} val
     */
    setURLPath: function(val) {
      urlPath = val;
    },

    /**
     * Gets the parameters component.
     * @returns {string}
     */
    getURLSearch: function() {
      initializeIfNecessary();
      return urlSearch;
    },

    /**
     * Sets the search component.
     * @param {string} val
     */
    setURLSearch: function(val) {
      urlSearch = val;
    },

    /**
     * Gets the fragment component.
     * @returns {string}
     */
    getURLFragment: function() {
      initializeIfNecessary();
      return urlFragment;
    },

    /**
     * Sets the fragment component.
     * @param {string} val
     */
    setURLFragment: function(val) {
      urlFragment = val;
    },


    /**
     * Checks if URI is present.
     * @returns {boolean}
     */
    isURIPresent: function() {
      return !!this.getURI();
    },

    /**
     * Checks if a parameter is present.
     * @param {string} paramName
     * @returns {boolean}
     */
    isParameterPresent: function(paramName) {
      return !!this.getParameter(paramName);
    },

    /**
     * Checks if a string is an ip address.
     * @param {string} string
     * @returns {boolean}
     */
    isIPv4Address: function(string) {
      return !!/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}/.test(string);
    },

    /**
     * Gets a parameter give it's name
     * @param {string} paramName
     * @returns {*}
     */
    getParameter: function(paramName) {
      initializeIfNecessary();
      return urlQueryParam[encodeURIComponent(paramName)];
    }

  };

  /**
   * Parses the uri set in the object.
   */
  function parseURI() {
    var urlElement = document.createElement('a');
    urlElement.href = self.getURI();

    self.setURLProtocol(urlElement.protocol);
    self.setURLHostname(urlElement.hostname);
    self.setURLPath(urlElement.pathname);
    self.setURLSearch(urlElement.search);
    self.setURLFragment(urlElement.hash);

    //parse url, set parameters
    if (urlSearch) {
      var query = urlSearch.substring(1);
      var query_items = query.split('&');
      for (var i = 0; i < query_items.length; i++) {
        var s = query_items[i].split('=');
        urlQueryParam[s[0]] = s[1];
      }
    }
  }

  return self;

})();
