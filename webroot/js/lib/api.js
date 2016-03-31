var $apiKey = {
    key : '7f81a2f5fe9365baa036c12f8204075c7ea8f37f'
};

angular.module('api', [])
.provider('$api', [function () {
	
    this.$get = ['$http', 'BatchCall', function ($http, BatchCall) {
       
        $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        $http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";
        $http.defaults.headers.common['If-Modified-Since'] = '0';
        $http.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        function call(method, url, cfg) { 
            
            var config = angular.extend({
                method: method,
                url: 'api/' + url,
                cache : false
            }, cfg);

            if(!config.params)
                config.params = {};
            
            config.params.key = $apiKey.key;
       
            return $http(config);
        };

        var API = {
            batch : function () {
                return new BatchCall();
            },
            
            get : function (url, params) {
                return call('GET', url, {
                    params: params
                });
            },
            
            post : function (url, params, data) {
                return call('POST', url, {
                    params: params,
                    data : data
                });
            },
            
            put : function (url, params, data) {
                return call('PUT', url, {
                    params: params,
                    data : data
                });
            },
            
            'delete' : function (url, params) {
                return call('DELETE', url, {
                    params: params
                });
            },
            
            call: function (method, url, config) {
                return call(method, url, config);
            }
        };

        return API;
    }];
    
/**
 * Loading factory
 * Catch all the promises of loading and requests (from $api) and shows them here.
 */
}]).factory('BatchCall', ['$http', '$q', function ($http, $q) {
    
    var bc = function () {
        var calls = [];
        var returnKeyCounter = 0;
        
        var findCallByKey = function (returnKey) {
            var result = null;
            
            angular.forEach(calls, function (value) {
                if(value.callObject.returnKey === returnKey)
                    result = value;
            });
            
            return result;
        };
        
        var handleResponse = function (def, data) {
            // is error, http status code is different than 200
            if(data.code && data.code !== 200) {
                def.reject(data);
            } else {
                def.resolve(data);
            }
        };
        
        this.execute = function () {
            var def = $q.defer();
            
            var sendArray = [];
            angular.forEach(calls, function (value) {
                sendArray.push(value.callObject);
            });
            
            $http.post('/api/batch?key='+$apiKey.key, sendArray).success(function (data, status, headers, config) {
                
                for(var returnKey in data) {
                    var foundObject = findCallByKey(returnKey);
                    if(foundObject) {
                        handleResponse(foundObject.promise, data[returnKey]);
                    }
                }
                
                def.resolve();
            }).catch(function (data, status, headers, config) {
                def.reject(data);
            });
            
            return def.promise;
        };
        
        this.call = function (url, method, params, data) {  
            method = method || "GET";
            if(!url) 
                throw new "URL or Method cannot be empty";
            
            var newDef = $q.defer();
            
            var callObject = {
                method : method,
                url : url,
                data : data ? data : null,
                params : params ? params : null,
                returnKey : 'rk'+(returnKeyCounter++)
            };
            
            calls.push({
                promise : newDef,
                callObject : callObject
            });
            
            return newDef.promise;
        };
        
    };
    
    return bc;
}]);