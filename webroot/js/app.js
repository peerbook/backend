var cachekey = document.querySelector('[data-cache-key]').attributes['data-cache-key'].value;
    
var app = angular.module('peerbieb', ['ngMaterial', 'angulartics', 'angulartics.google.analytics', 'api']);
app.config([
    '$controllerProvider',
    '$compileProvider',
    '$filterProvider',
    '$provide',
    '$injector',
    function ($controllerProvider, $compileProvider, $filterProvider, $provide, $injector) {

        // provider aliases
        app.controller = $controllerProvider.register;
        app.directive = $compileProvider.directive;
        app.filter = $filterProvider.register;
        app.factory = $provide.factory;
        app.service = $provide.service;
        app._providerObject = {
            $compileProvider: $compileProvider,
            $controllerProvider: $controllerProvider,
            $filterProvider: $filterProvider,
            $provide: $provide,
            $injector: $injector
        };   
        
        WebFont.load({
            google: {
                families: ['Roboto']
            }
        });
    }
]);