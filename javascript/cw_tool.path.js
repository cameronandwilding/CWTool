/**
 * @file
 *  Add path related behaviours.
 */
var CW = CW || {};

(function($) {

  'use strict';
    
  CW.Path = {

    initialized: false,
    
    uri: document.location.href,
    
    urlProtocol: '',
    urlHostname: '',
    urlPath: '',
    urlSearch: '',
    urlQueryParam: [],
    urlFragment: '',
    
    initializeIfNecessary: function() {
      if( !this.initialized ) {
        this.parseURI();
        this.initialized = true;
      }
    },    
    
    getURI: function() {
      return this.uri;
    },
    
    setURI: function( uri ) {
      this.uri = uri;
    },
    
    getUrlProtocol: function() {
      this.initializeIfNecessary();
      return this.urlProtocol;  
    },
    
    setUrlProtocol: function( val ) {
      this.urlProtocol = val;
    },
    
    getUrlHostname: function() {
      this.initializeIfNecessary();
      return this.urlHostname;  
    },

    getUrlBaseHostname: function() {
      if (!this.isIPAddress(this.getUrlHostname())) {
        var components = this.getUrlHostname().split('.');
        return components.slice(-2).join('.');
      }
      return this.getUrlHostname();
    },
    
    setUrlHostname: function( val ) {
      this.urlHostname = val;
    },
    
    getUrlPath: function() {
      this.initializeIfNecessary();
      return this.urlPath;  
    },
    
    setUrlPath: function( val ) {
      this.urlPath = val;
    },
    
    getUrlSearch: function() {
      this.initializeIfNecessary();
      return this.urlSearch;  
    },
    
    setUrlSearch: function( val ) {
      this.urlSearch = val;
    },
    
    getUrlFragment: function() {
      this.initializeIfNecessary();
      return this.urlFragment;  
    },
    
    setUrlFragment: function( val ) {
      this.urlFragment = val;
    },
    
    //Parameters
    parseURI: function() {
      var urlElement = document.createElement('a');
      urlElement.href = this.getURI();

      this.setUrlProtocol(urlElement.protocol);
      this.setUrlHostname(urlElement.hostname);
      this.setUrlPath(urlElement.pathname);
      this.setUrlSearch(urlElement.search);
      this.setUrlFragment(urlElement.hash);

      //parse url, set parameters
      if (this.urlSearch) {
        var query = this.urlSearch.substring(1);
        var query_items = query.split('&');
        for (var i = 0; i < query_items.length; i++){
          var s = query_items[i].split('='); 
          this.urlQueryParam[s[0]] = s[1];
        }
      }
    },
    
    isURIPresent: function() {
      return (this.getURI()) ? true : false;
    },
    
    isParameterPresent: function( paramName ){
      return (this.getParameter( paramName ));
    },

    isIPAddress: function(string) {
      return (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(string));
    },

    getParameter: function( paramName ) {
      this.initializeIfNecessary();
      return this.urlQueryParam[paramName];
    },

    appendParameters: function(url, parameters) {
      var string = $.param(parameters);
      if (string) {
        return url + (url.indexOf('?') > 0 ? '&' : '?') + string;
      }
      return url;
    }
  }
}(jQuery));