<!doctype html>
<html ng-app="app">
    <head>
        <meta charset="utf-8">

        <base href="<?php echo Yii::app()->getBaseUrl(true); ?>/">
	
	<!-- Angular Material Dependencies -->
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-animate.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-aria.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angular_material/0.9.0/angular-material.min.js"></script>
	
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/0.9.0/angular-material.min.css">
	
	<style>
	    html, body {
		font-family: 'Roboto', sans-serif;
		font-size: 14px;
		height: 100%;
		margin: 0px;
		padding: 0px;
	    }

	    md-toolbar h1 {
		margin: auto;
	    }

	    .md-docs-dark-theme h2 {
		color: #fff;
		padding: 3px 0;
	    }

	    .loader {
		height: 5px;
		&.md-default-theme md-progress-linear {
		    position: relative;
		    top: -5px;
		}
	    }

	    .mg-input-equalize {
		padding-bottom: 34px;
	    }
	    .mg-input-equalize-margin {
		margin-bottom: 35px;
	    }

	    #wrapper {
		margin: 0 auto;
		margin-top: 100px;
		width: 700px;
	    }

	    .code {
		background: #eaeaea;
		word-wrap: break-word;
	    }
	</style>

        <title>Peerbieb Material Explorer</title>
    </head>

    <body ng-controller="ExplorerController">

	<div id="wrapper" class="md-whiteframe-z2" layout="column" >
	    
	    <md-toolbar>
		<h1>API Explorer</h1>
	    </md-toolbar>

	    <md-content layout-padding layout="column" md-theme="docs-dark">
		<form name="apiform" layout="column" >
		    
		    <div layout="row" layout-margin layout-align="space-between end">
			<md-select ng-model="method" class="mg-input-equalize">
			    <md-option ng-value="opt" ng-repeat="opt in ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD']">{{ opt }}</md-option>
			</md-select>

			<md-input-container flex>
			    <label>URL</label>
			    <input type="text" ng-model="url" required placeholder="/api/">
			</md-input-container>

			<md-button class="md-raised md-primary mg-input-equalize-margin" ng-click="api()" >
			    Send
			</md-button>
		    </div>
		    
		    <div layout="column" layout-margin ng-if="method != 'GET' ">
			
			<div>
			    <h2>Params</h2>
			    <md-button ng-click="addField()" class="md-raised md-primary" >
				Add Field
			    </md-button>
			</div>
			
			<md-list>
			    <md-list-item layout="row" ng-repeat="field in fields">
				
				<md-input-container>
				    <label>Key</label>
				    <input type="text" ng-model="field.key" required placeholder="/api/">
				</md-input-container>
				
				<md-input-container>
				    <label>Value</label>
				    <input type="text" ng-model="field.value" required placeholder="/api/">
				</md-input-container>
				
				<md-checkbox ng-model="field.json" aria-label="Is Json">
				    JSON
				</md-checkbox>
				
				<md-button ng-click="remove(field)" class="md-primary">
				    Delete
				</md-button>
				
			    </md-list-item>
			</md-list>
			
		    </div>
		    
		</form>
	    </md-content>
	    
	    <md-content layout="column" class="loader" ng-if="loading">
		<md-progress-linear md-mode="indeterminate"></md-progress-linear>
	    </md-content>
	    
	    <md-content layout="column">
		
		<div layout-margin layout-padding class="md-whiteframe-z1" >
		    Loaded in {{time}} ms with status {{status}}
		</div>
		
		<div layout-margin layout-padding class="md-whiteframe-z1 code" ng-bind-html="output | wrap | safe"></div>

	    </md-content>
	
	</div>

	<script type="text/javascript">
	    
	    angular.module('filters', []).filter('wrap', function() {
		return function(obj) {
		    if(!obj)
			return '';
		    
		    if(obj.toString().split('<').length > 10) {
			return obj;
		    }
		    return '<pre>'+JSON.stringify(obj, null, 4)+'</pre>';
		};
	    }).filter('safe', ['$sce', function ($sce) {
		return function (text) {
		    return $sce.trustAsHtml(text);
		};
	    }]);;

	    var app = angular.module('app', ['filters', 'ngMaterial']);
	    
	    app.config(function($mdThemingProvider){
		
		 $mdThemingProvider.theme('default')
		    .primaryPalette('green')
		    .accentPalette('orange');
		
		$mdThemingProvider.theme('docs-dark', 'default')
		    .primaryPalette('light-blue')
		    .dark();	
	    });
	    
	    app.controller('ExplorerController', ['$scope', '$http', '$sce', function ($scope, $http, $sce) {

		$scope.loading = false;

		$scope.output = '';
		$scope.fields = [{key:'',value:''}];
		$scope.method = 'GET';
		$scope.token = '7f81a2f5fe9365baa036c12f8204075c7ea8f37f';
		$scope.url = 'index.php/api/';
		
		$scope.time = 0;

		$scope.status = 0;

		$scope.remove = function (field) {
		    $scope.fields = $scope.fields.filter(function (v) {
			return v.key !== field.key;
		    });
		};
		$scope.addField = function () {
		    $scope.fields.push({key:'',value:''});
		};

		function api(method, url, params) {
		    var t0 = performance.now();
		    if(url.substring(0,1) == '/') {
			url = url.substring(1);
		    }

		    // reset token
		    if(url === 'api/auth/get') {
			$scope.token = '';
		    }

		    $http.defaults.headers.common['X-Spull-Api-Key'] = $scope.token;
		    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

		    var succes = function (data, status) {
			$scope.loading = false;

			$scope.status = status;
			$scope.output = data;

			var t1 = performance.now();
			$scope.time = Math.round(t1 - t0);
		    };
		    var error = function (data, status, headers, config) {
			$scope.loading = false;
			$scope.status = status;
			$scope.output = data;

			var t1 = performance.now();
			$scope.time = Math.round(t1 - t0);
		    };

		    // Get
		    if(method == "GET") {
			$scope.loading = true;
			$http.get(url).success(succes).error(error);
		    } else {
			var cb = null;
			switch(method) { 
			    case "POST": cb = $http.post; break;
			    case "PUT": cb = $http.put; break;
			    case "DELETE": cb = $http['delete']; break;
			}

			if(cb !== null) {
			    $scope.loading = true;
			    cb(url, params).success(succes).error(error);
			}
		    }
		}

		$scope.api = function () {
		    var data = {};
		    $scope.fields.forEach(function (v) {
			if(v.json) {
			    data[v.key] = JSON.parse(v.value);
			} else {
			    data[v.key] = v.value;
			}
		    });

		    api($scope.method, $scope.url, data);
		};

		$scope.call = function (method, url, params) {
		    
		    $scope.url = url;
		    $scope.method = method;

		    var o = [];
		    params.forEach(params, function (v, k) {
			o.push({'key':k,'value':v});
		    });

		    $scope.fields = o;

		    api(method, url, params);
		};

	    }]);
	</script>
	    
    </body>
</html>