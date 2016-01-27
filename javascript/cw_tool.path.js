/**
 * @file
 *  Add path related behaviours.
 */
var CW = CW || {};

(function () {

  'use strict';

  /**
   * @memberof CW
   * @property {object} Path - Handler to get handle path information from javascript.
   */
  CW.Path = {

    /**
     * If the path components have been _initialized or not.
     * @type {bool}
     */
    _initialized: false,

    /**
     * Stores the uri to be used.
     * @type {string}
     */
    uri: document.location.href,

    /**
     * Procotol component.
     * @type {string}
     */
    _urlProtocol: '',

    /**
     * Hostname component.
     * @type {string}
     */
    _urlHostname: '',

    /**
     * Path component.
     * @type {string}
     */
    _urlPath: '',

    /**
     * Parameters component.
     * @type {string}
     */
    _urlSearch: '',

    /**
     * Parsed parameters component.
     * @type {Array}
     */
    _urlQueryParam: [],

    /**
     * Fragment component.
     * @type {string}
     */
    _urlFragment: '',

    /**
     * Initializes the components if necessary.
     */
    initializeIfNecessary: function() {
      if (!this._initialized) {
        this._parseURI();
        this._initialized = true;
      }
    },

    /**
     * Gets the URI component.
     * @returns {string}
     */
    getURI: function() {
      return this.uri;
    },

    /**
     * Sets the URI component.
     * @param {string} uri
     */
    _setURI: function(uri) {
      this.uri = uri;
    },

    /**
     * Gets the Protocol component.
     * @returns {string}
     */
    get_urlProtocol: function() {
      this.initializeIfNecessary();
      return this._urlProtocol;
    },

    /**
     * Sets the protocol component.
     * @param {string} val
     */
    _set_urlProtocol: function(val) {
      this._urlProtocol = val;
    },

    /**
     * Gets the hostname component.
     * @returns {string}
     */
    get_urlHostname: function() {
      this.initializeIfNecessary();
      return this._urlHostname;
    },

    /**
     * Gets the base hostname component.
     * @returns {string}
     */
    getUrlBaseHostname: function() {
      if (!this.isIPv4Address(this.get_urlHostname())) {
        var components = this.get_urlHostname().split('.');
        return components.slice(-2).join('.');
      }
      return this.get_urlHostname();
    },

    /**
     * Sets the hostname component.
     * @param {string} val
     */
    _set_urlHostname: function(val) {
      this._urlHostname = val;
    },

    /**
     * Gets the path component.
     * @returns {string}
     */
    get_urlPath: function() {
      this.initializeIfNecessary();
      return this._urlPath;
    },

    /**
     * Sets the path component.
     * @param {string} val
     */
    _set_urlPath: function(val) {
      this._urlPath = val;
    },

    /**
     * Gets the parameters component.
     * @returns {string}
     */
    get_urlSearch: function() {
      this.initializeIfNecessary();
      return this._urlSearch;
    },

    /**
     * Sets the search component.
     * @param {string} val
     */
    _set_urlSearch: function(val) {
      this._urlSearch = val;
    },

    /**
     * Gets the fragment component.
     * @returns {string}
     */
    get_urlFragment: function() {
      this.initializeIfNecessary();
      return this._urlFragment;
    },

    /**
     * Sets the fragment component.
     * @param {string} val
     */
    _set_urlFragment: function(val) {
      this._urlFragment = val;
    },

    /**
     * Parses the uri set in the object.
     */
    _parseURI: function() {
      var urlElement = document.createElement('a');
      urlElement.href = this.getURI();

      this._set_urlProtocol(urlElement.protocol);
      this._set_urlHostname(urlElement.hostname);
      this._set_urlPath(urlElement.pathname);
      this._set_urlSearch(urlElement.search);
      this._set_urlFragment(urlElement.hash);

      //parse url, set parameters
      if (this._urlSearch) {
        var query = this._urlSearch.substring(1);
        var query_items = query.split('&');
        for (var i = 0; i < query_items.length; i++) {
          var s = query_items[i].split('=');
          this._urlQueryParam[s[0]] = s[1];
        }
      }
    },

    /**
     * Checks if URI is present.
     * @returns {boolean}
     */
    isURIPresent: function() {
      return Boolean(this.getURI());
    },

    /**
     * Checks if a parameter is present.
     * @param {string} paramName
     * @returns {boolean}
     */
    isParameterPresent: function(paramName){
      return Boolean(this.getParameter(paramName));
    },

    /**
     * Checks if a string is an ip address.
     * @param {string} string
     * @returns {boolean}
     */
    isIPv4Address: function(string) {
      return Boolean(/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}/.test(string));
    },

    /**
     * Gets a parameter give it's name
     * @param {string} paramName
     * @returns {*}
     */
    getParameter: function(paramName) {
      this.initializeIfNecessary();
      return unescape(this._urlQueryParam[paramName]);
    }
  };

})();