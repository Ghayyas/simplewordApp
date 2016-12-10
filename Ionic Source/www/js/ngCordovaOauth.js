(function() {
  'use strict';

  angular.module('oauth.facebook', ['oauth.utils'])
    .factory('$ngCordovaFacebook', facebook);

  function facebook($q, $http, $cordovaOauthUtility) {
    return { signin: oauthFacebook };

    /*
     * Sign into the Facebook service
     *
     * @param    string clientId
     * @param    array appScope
     * @param    object options
     * @return   promise
     */
    function oauthFacebook(clientId, appScope, options) {
      var deferred = $q.defer();
      if(window.cordova) {
        if($cordovaOauthUtility.isInAppBrowserInstalled()) {
          var redirect_uri = "http://localhost:8100/oauthcallback.html";
          if(options !== undefined) {
            if(options.hasOwnProperty("redirect_uri")) {
              redirect_uri = options.redirect_uri;
            }
          }
          var flowUrl = "https://www.facebook.com/v2.6/dialog/oauth?client_id=" + clientId + "&redirect_uri=" + redirect_uri + "&response_type=token&scope=" + appScope.join(",");
          if(options !== undefined && options.hasOwnProperty("auth_type")) {
            flowUrl += "&auth_type=" + options.auth_type;
          }
          var browserRef = window.cordova.InAppBrowser.open(flowUrl, '_blank', 'location=no,clearsessioncache=yes,clearcache=yes');
          browserRef.addEventListener('loadstart', function(event) {
            if((event.url).indexOf(redirect_uri) === 0) {
              browserRef.removeEventListener("exit",function(event){});
              browserRef.close();
              var callbackResponse = (event.url).split("#")[1];
              var responseParameters = (callbackResponse).split("&");
              var parameterMap = [];
              for(var i = 0; i < responseParameters.length; i++) {
                parameterMap[responseParameters[i].split("=")[0]] = responseParameters[i].split("=")[1];
              }
              if(parameterMap.access_token !== undefined && parameterMap.access_token !== null) {
                deferred.resolve({ access_token: parameterMap.access_token, expires_in: parameterMap.expires_in });
              } else {
                if ((event.url).indexOf("error_code=100") !== 0) {
                  deferred.reject("Facebook returned error_code=100: Invalid permissions");
                } else {
                  deferred.reject("Problem authenticating");
                }
              }
            }
          });
          browserRef.addEventListener('exit', function(event) {
            deferred.reject("The sign in flow was canceled");
          });
        } else {
          deferred.reject("Could not find InAppBrowser plugin");
        }
      } else {
        deferred.reject("Cannot authenticate via a web browser");
      }
      return deferred.promise;
    }
  }

  facebook.$inject = ['$q', '$http', '$cordovaOauthUtility'];
})();
(function() {
  'use strict';

  angular.module('oauth.google', ['oauth.utils'])
    .factory('$ngCordovaGoogle', google);

  function google($q, $http, $cordovaOauthUtility) {
    return { signin: oauthGoogle };

    /*
     * Sign into the Google service
     *
     * @param    string clientId
     * @param    array appScope
     * @param    object options
     * @return   promise
     */
    function oauthGoogle(clientId, appScope, options) {
      var deferred = $q.defer();
      if(window.cordova) {
        if($cordovaOauthUtility.isInAppBrowserInstalled()) {
          var redirect_uri = "http://localhost:8100/oauthcallback.html";
          if(options !== undefined) {
            if(options.hasOwnProperty("redirect_uri")) {
              redirect_uri = options.redirect_uri;
            }
          }
          var browserRef = window.cordova.InAppBrowser.open('https://accounts.google.com/o/oauth2/auth?client_id=' + clientId + '&redirect_uri=' + redirect_uri + '&scope=' + appScope.join(" ") + '&approval_prompt=force&response_type=token id_token', '_blank', 'location=no,clearsessioncache=yes,clearcache=yes');
          browserRef.addEventListener("loadstart", function(event) {
            if((event.url).indexOf(redirect_uri) === 0) {
              browserRef.removeEventListener("exit",function(event){});
              browserRef.close();
              var callbackResponse = (event.url).split("#")[1];
              var responseParameters = (callbackResponse).split("&");
              var parameterMap = [];
              for(var i = 0; i < responseParameters.length; i++) {
                parameterMap[responseParameters[i].split("=")[0]] = responseParameters[i].split("=")[1];
              }
              if(parameterMap.access_token !== undefined && parameterMap.access_token !== null) {
                deferred.resolve({ access_token: parameterMap.access_token, token_type: parameterMap.token_type, expires_in: parameterMap.expires_in, id_token: parameterMap.id_token });
              } else {
                deferred.reject("Problem authenticating");
              }
            }
          });
          browserRef.addEventListener('exit', function(event) {
            deferred.reject("The sign in flow was canceled");
          });
        } else {
          deferred.reject("Could not find InAppBrowser plugin");
        }
      } else {
        deferred.reject("Cannot authenticate via a web browser");
      }
      return deferred.promise;
    }
  }

  google.$inject = ['$q', '$http', '$cordovaOauthUtility'];
})();
(function() {
  'use strict';

  angular.module('oauth.instagram', ['oauth.utils'])
    .factory('$ngCordovaInstagram', instagram);

  function instagram($q, $http, $cordovaOauthUtility) {
    return { signin: oauthInstagram };

    /*
     * Sign into the Instagram service
     *
     * @param    string clientId
     * @param    array appScope
     * @param    object options
     * @return   promise
     */
    function oauthInstagram(clientId, appScope, options) {
      var deferred = $q.defer();
      var split_tokens = {
          'code':'?',
          'token':'#'
      };

      if(window.cordova) {
        if($cordovaOauthUtility.isInAppBrowserInstalled()) {
          var redirect_uri = "http://localhost:8100/oauthcallback.html";
          var response_type = "token";
          if(options !== undefined) {
            if(options.hasOwnProperty("redirect_uri")) {
              redirect_uri = options.redirect_uri;
            }
            if(options.hasOwnProperty("response_type")) {
              response_type = options.response_type;
            }
          }

          var scope = '';
          if (appScope && appScope.length > 0) {
            scope = '&scope' + appScope.join('+');
          }

          var browserRef = window.cordova.InAppBrowser.open('https://api.instagram.com/oauth/authorize/?client_id=' + clientId + '&redirect_uri=' + redirect_uri + scope + '&response_type='+response_type, '_blank', 'location=no,clearsessioncache=yes,clearcache=yes');
          browserRef.addEventListener('loadstart', function(event) {
            if((event.url).indexOf(redirect_uri) === 0) {
                browserRef.removeEventListener("exit",function(event){});
                browserRef.close();
                var callbackResponse = (event.url).split(split_tokens[response_type])[1];
                var parameterMap = $cordovaOauthUtility.parseResponseParameters(callbackResponse);
                if(parameterMap.access_token) {
                  deferred.resolve({ access_token: parameterMap.access_token });
                } else if(parameterMap.code !== undefined && parameterMap.code !== null) {
                  deferred.resolve({ code: parameterMap.code });
                } else {
                  deferred.reject("Problem authenticating");
                }
            }
          });
          browserRef.addEventListener('exit', function(event) {
              deferred.reject("The sign in flow was canceled");
          });
        } else {
            deferred.reject("Could not find InAppBrowser plugin");
        }
      } else {
        deferred.reject("Cannot authenticate via a web browser");
      }

      return deferred.promise;
    }
  }

  instagram.$inject = ['$q', '$http', '$cordovaOauthUtility'];
})();
(function() {
  'use strict';

  angular.module('oauth.twitter', ['oauth.utils'])
    .factory('$ngCordovaTwitter', twitter);

  function twitter($q, $http, $cordovaOauthUtility) {
    return { signin: oauthTwitter };

    /*
     * Sign into the Twitter service
     * Note that this service requires jsSHA for generating HMAC-SHA1 Oauth 1.0 signatures
     *
     * @param    string clientId
     * @param    string clientSecret
     * @return   promise
     */
    function oauthTwitter(clientId, clientSecret, options) {
      var deferred = $q.defer();
      if(window.cordova) {
        if($cordovaOauthUtility.isInAppBrowserInstalled()) {
          var redirect_uri = "http://localhost:8100/oauthcallback.html";
          if(options !== undefined) {
            if(options.hasOwnProperty("redirect_uri")) {
                redirect_uri = options.redirect_uri;
            }
          }

          if(typeof jsSHA !== "undefined") {
            var oauthObject = {
              oauth_consumer_key: clientId,
              oauth_nonce: $cordovaOauthUtility.createNonce(10),
              oauth_signature_method: "HMAC-SHA1",
              oauth_timestamp: Math.round((new Date()).getTime() / 1000.0),
              oauth_version: "1.0"
            };
            var signatureObj = $cordovaOauthUtility.createSignature("POST", "https://api.twitter.com/oauth/request_token", oauthObject,  { oauth_callback: redirect_uri }, clientSecret);
            $http({
              method: "post",
              url: "https://api.twitter.com/oauth/request_token",
              headers: {
                  "Authorization": signatureObj.authorization_header,
                  "Content-Type": "application/x-www-form-urlencoded"
              },
              data: "oauth_callback=" + encodeURIComponent(redirect_uri)
            })
              .success(function(requestTokenResult) {
                var requestTokenParameters = (requestTokenResult).split("&");
                var parameterMap = {};
                for(var i = 0; i < requestTokenParameters.length; i++) {
                  parameterMap[requestTokenParameters[i].split("=")[0]] = requestTokenParameters[i].split("=")[1];
                }
                if(parameterMap.hasOwnProperty("oauth_token") === false) {
                  deferred.reject("Oauth request token was not received");
                }
                var browserRef = window.cordova.InAppBrowser.open('https://api.twitter.com/oauth/authenticate?oauth_token=' + parameterMap.oauth_token, '_blank', 'location=no,clearsessioncache=yes,clearcache=yes');
                browserRef.addEventListener('loadstart', function(event) {
                  if((event.url).indexOf(redirect_uri) === 0) {
                    var callbackResponse = (event.url).split("?")[1];
                    var responseParameters = (callbackResponse).split("&");
                    var parameterMap = {};
                    for(var i = 0; i < responseParameters.length; i++) {
                      parameterMap[responseParameters[i].split("=")[0]] = responseParameters[i].split("=")[1];
                    }
                    if(parameterMap.hasOwnProperty("oauth_verifier") === false) {
                      deferred.reject("Browser authentication failed to complete.  No oauth_verifier was returned");
                    }
                    delete oauthObject.oauth_signature;
                    oauthObject.oauth_token = parameterMap.oauth_token;
                    var signatureObj = $cordovaOauthUtility.createSignature("POST", "https://api.twitter.com/oauth/access_token", oauthObject,  { oauth_verifier: parameterMap.oauth_verifier }, clientSecret);
                    $http({
                      method: "post",
                      url: "https://api.twitter.com/oauth/access_token",
                      headers: {
                          "Authorization": signatureObj.authorization_header
                      },
                      params: {
                          "oauth_verifier": parameterMap.oauth_verifier
                      }
                    })
                      .success(function(result) {
                        var accessTokenParameters = result.split("&");
                        var parameterMap = {};
                        for(var i = 0; i < accessTokenParameters.length; i++) {
                          parameterMap[accessTokenParameters[i].split("=")[0]] = accessTokenParameters[i].split("=")[1];
                        }
                        if(parameterMap.hasOwnProperty("oauth_token_secret") === false) {
                          deferred.reject("Oauth access token was not received");
                        }
                        deferred.resolve(parameterMap);
                      })
                      .error(function(error) {
                        deferred.reject(error);
                      })
                      .finally(function() {
                        setTimeout(function() {
                            browserRef.close();
                        }, 10);
                      });
                  }
                });
                browserRef.addEventListener('exit', function(event) {
                  deferred.reject("The sign in flow was canceled");
                });
              })
              .error(function(error) {
                deferred.reject(error);
              });
          } else {
              deferred.reject("Missing jsSHA JavaScript library");
          }
        } else {
            deferred.reject("Could not find InAppBrowser plugin");
        }
      } else {
        deferred.reject("Cannot authenticate via a web browser");
      }

      return deferred.promise;
    }
  }

  twitter.$inject = ['$q', '$http', '$cordovaOauthUtility'];
})();
(function() {
  'use strict';

  angular.module("oauth.providers", [
    "oauth.utils",
    'oauth.google',
    'oauth.facebook',
    'oauth.instagram',
    'oauth.twitter'])
    .factory("$cordovaOauth", cordovaOauth);

  function cordovaOauth(
    $q, $http, $cordovaOauthUtility, $ngCordovaGoogle, $ngCordovaFacebook, $ngCordovaInstagram, $ngCordovaTwitter) {

    return {
      google: $ngCordovaGoogle.signin,
      facebook: $ngCordovaFacebook.signin,
      instagram: $ngCordovaInstagram.signin,
      twitter: $ngCordovaTwitter.signin
    };
  }

  cordovaOauth.$inject = [
    "$q", '$http', '$cordovaOauthUtility',
    '$ngCordovaGoogle',
    '$ngCordovaFacebook',
    '$ngCordovaInstagram',
    '$ngCordovaTwitter'
  ];
})();
(function() {
  angular.module("oauth.utils", [])
    .factory("$cordovaOauthUtility", cordovaOauthUtility);

  function cordovaOauthUtility($q) {
    return {
      isInAppBrowserInstalled: isInAppBrowserInstalled,
      createSignature: createSignature,
      createNonce: createNonce,
      generateUrlParameters: generateUrlParameters,
      parseResponseParameters: parseResponseParameters,
      generateOauthParametersInstance: generateOauthParametersInstance
    };

    /*
     * Check to see if the mandatory InAppBrowser plugin is installed
     *
     * @param
     * @return   boolean
     */
    function isInAppBrowserInstalled() {
      var cordovaPluginList = cordova.require("cordova/plugin_list");
      var inAppBrowserNames = ["cordova-plugin-inappbrowser", "cordova-plugin-inappbrowser.inappbrowser", "org.apache.cordova.inappbrowser"];

      if (Object.keys(cordovaPluginList.metadata).length === 0) {
        var formatedPluginList = cordovaPluginList.map(
          function(plugin) {
            return plugin.id || plugin.pluginId;
          });

        return inAppBrowserNames.some(function(name) {
          return formatedPluginList.indexOf(name) != -1 ? true : false;
        });
      } else {
        return inAppBrowserNames.some(function(name) {
          return cordovaPluginList.metadata.hasOwnProperty(name);
        });
      }
    }

    /*
     * Sign an Oauth 1.0 request
     *
     * @param    string method
     * @param    string endPoint
     * @param    object headerParameters
     * @param    object bodyParameters
     * @param    string secretKey
     * @param    string tokenSecret (optional)
     * @return   object
     */
    function createSignature(method, endPoint, headerParameters, bodyParameters, secretKey, tokenSecret) {
      if(typeof jsSHA !== "undefined") {
        var headerAndBodyParameters = angular.copy(headerParameters);
        var bodyParameterKeys = Object.keys(bodyParameters);

        for(var i = 0; i < bodyParameterKeys.length; i++) {
          headerAndBodyParameters[bodyParameterKeys[i]] = encodeURIComponent(bodyParameters[bodyParameterKeys[i]]);
        }

        var signatureBaseString = method + "&" + encodeURIComponent(endPoint) + "&";
        var headerAndBodyParameterKeys = (Object.keys(headerAndBodyParameters)).sort();

        for(i = 0; i < headerAndBodyParameterKeys.length; i++) {
          if(i == headerAndBodyParameterKeys.length - 1) {
            signatureBaseString += encodeURIComponent(headerAndBodyParameterKeys[i] + "=" + headerAndBodyParameters[headerAndBodyParameterKeys[i]]);
          } else {
            signatureBaseString += encodeURIComponent(headerAndBodyParameterKeys[i] + "=" + headerAndBodyParameters[headerAndBodyParameterKeys[i]] + "&");
          }
        }

        var oauthSignatureObject = new jsSHA(signatureBaseString, "TEXT");

        var encodedTokenSecret = '';
        if (tokenSecret) {
          encodedTokenSecret = encodeURIComponent(tokenSecret);
        }

        headerParameters.oauth_signature = encodeURIComponent(oauthSignatureObject.getHMAC(encodeURIComponent(secretKey) + "&" + encodedTokenSecret, "TEXT", "SHA-1", "B64"));
        var headerParameterKeys = Object.keys(headerParameters);
        var authorizationHeader = 'OAuth ';

        for(i = 0; i < headerParameterKeys.length; i++) {
          if(i == headerParameterKeys.length - 1) {
            authorizationHeader += headerParameterKeys[i] + '="' + headerParameters[headerParameterKeys[i]] + '"';
          } else {
            authorizationHeader += headerParameterKeys[i] + '="' + headerParameters[headerParameterKeys[i]] + '",';
          }
        }

        return { signature_base_string: signatureBaseString, authorization_header: authorizationHeader, signature: headerParameters.oauth_signature };
      } else {
        return "Missing jsSHA JavaScript library";
      }
    }

    /*
    * Create Random String Nonce
    *
    * @param    integer length
    * @return   string
    */
    function createNonce(length) {
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for(var i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }

      return text;
    }

    function generateUrlParameters(parameters) {
      var sortedKeys = Object.keys(parameters);
      sortedKeys.sort();

      var params = "";
      var amp = "";

      for (var i = 0 ; i < sortedKeys.length; i++) {
        params += amp + sortedKeys[i] + "=" + parameters[sortedKeys[i]];
        amp = "&";
      }

      return params;
    }

    function parseResponseParameters(response) {
      if (response.split) {
        var parameters = response.split("&");
        var parameterMap = {};

        for(var i = 0; i < parameters.length; i++) {
            parameterMap[parameters[i].split("=")[0]] = parameters[i].split("=")[1];
        }

        return parameterMap;
      }
      else {
        return {};
      }
    }

    function generateOauthParametersInstance(consumerKey) {
      var nonceObj = new jsSHA(Math.round((new Date()).getTime() / 1000.0), "TEXT");
      var oauthObject = {
          oauth_consumer_key: consumerKey,
          oauth_nonce: nonceObj.getHash("SHA-1", "HEX"),
          oauth_signature_method: "HMAC-SHA1",
          oauth_timestamp: Math.round((new Date()).getTime() / 1000.0),
          oauth_version: "1.0"
      };
      return oauthObject;
    }
  }

  cordovaOauthUtility.$inject = ['$q'];
})();
angular.module("ngCordovaOauth", [
    "oauth.providers",
    "oauth.utils"
]);