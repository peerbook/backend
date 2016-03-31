/**
 * Angular module loader
 * Using requirejs
 * 
 * @author Han van der Veen
 */
var module_loader = (function () {

    /** Default settings of this loader */
    var settings = {
        mainModule: 'app'
    };

    /** @var {array} list of modules which are already loaded */
    var loadedModules = [];

    /**
     * This register and init function is partly copied from https://github.com/ocombe/ocLazyLoad
     * Improved for config callback and returning the modules  
     */
    function init() {
        loadedModules = getDependencies(settings.mainModule);
    }

    /**
     * Get dependencies of the module
     * @return {array} list of modules it depends on
     */
    function getDependencies(mod) {
        var requiredModules = ['ng'];

        // recursive load the modules
        (function addReg(module) {
            requiredModules.push(module);
            angular.forEach(angular.module(module).requires, function (m) {
                addReg(m);
            });
        })(mod);
        return requiredModules;
    }

    /**
     * Register the module to the providers
     * @param {object} providers Providers object
     * @param {array} registerModules modules to load
     * @param {function} configCallback The config callback that is used before init this module
     * @return {array} list of module objects
     */
    function register(providers, registerModules, configCallback) {
        var k, moduleName, moduleFn, provider;
        var returnModules = [];

        if (registerModules && registerModules.length > 0) {
            var runBlocks = [];
            for (k = registerModules.length - 1; k >= 0; k--) {

                moduleName = registerModules[k];
                moduleFn = angular.module(moduleName);
                
                returnModules.push(moduleFn);

                // Check if the module is already loaded
                if (loadedModules.indexOf(moduleName) >= 0) {
                    continue;
                }

                // Load dependencies
                angular.forEach(getDependencies(moduleName), function (module) {
                    if (loadedModules.indexOf(module) === -1 && moduleName !== module) {
                        register(providers, [module]);
                    }
                });

                // register config callback
                if (configCallback) {
                    moduleFn.config(configCallback);
                }

                function runInvokeQueue(queue) {
                    
                    if(!queue)
                        return;
                    
                    var i, ii;
                    for (i = 0, ii = queue.length; i < ii; i++) {
                        var invokeArgs = queue[i];
                        if (providers.hasOwnProperty(invokeArgs[0])) {
                            provider = providers[invokeArgs[0]];
                        } else {
                            throw "unsupported provider " + invokeArgs[0];
                        }

                        provider[invokeArgs[1]].apply(provider, invokeArgs[2]);
                    }
                }

                runBlocks = runBlocks.concat(moduleFn._runBlocks);
                try {
                    runInvokeQueue(moduleFn._invokeQueue);
                    runInvokeQueue(moduleFn._configBlocks);
                } catch (e) {
                    if (e.message)
                        e.message += ' from ' + moduleName;
                    
                    console.log(e);
                    
                    throw e;
                }
                registerModules.pop();

                // loaded correctly!
                loadedModules.push(moduleName);
            }
            
            // get active and current injector
            var $injector = angular.element(document).injector();
            
            // run the module (config and run part)
            angular.forEach(runBlocks, function (fn) {
                $injector.invoke(fn);
            });

        }
        return returnModules;
    }
    ;

    return {
        
        injector : function () {
            return angular.element(document).injector();
        },
        
        /**
         * Set the settings 
         * @param {object} settings that can be set
         * @returns settings object 
         */
        init: function (set) {
            return angular.extend(settings, set || {});
        },
        /**
         * Load dependencies using require
         * @param {array} dependencies list of files to be loaded
         * @return {promise}
         */
        dependencies: function (dependencies) {
            return {
                resolver: ['$q', '$rootScope', function ($q, $rootScope) {
                    var deferred = $q.defer();
                    require(dependencies, function () {
                        $rootScope.$apply(function () {
                            deferred.resolve();
                        });
                    });
                    return deferred.promise;
                }]
            };
        },
        /**
         * Load a external module into the current one
         * @param {string} module name (files already loaded)
         * @param {array} configCallback optional callback when a config is needed for the module
         * @returns {array|module}
         */
        module: function (module, configCallback) {

            // init the loader
            if (loadedModules.length === 0) {
                init();
            }

            var sourceModule = angular.module(settings.mainModule);

            var modules = module;
            if (angular.isString(module)) {
                modules = [module];
            }

            // load them
            var returnModules = register(sourceModule._providerObject, modules, configCallback);

            return (modules.length === 1) ? returnModules[0] : returnModules;
        }
    };
})();