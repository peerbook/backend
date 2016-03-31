module.exports = function (config) {
    config.set({
        basePath: './',
        files: [
            '../webroot/bower_components/angular/angular.js',
            '../webroot/bower_components/angular-route/angular-route.js',
            '../webroot/bower_components/angular-aria/angular-aria.js',
            '../webroot/bower_components/angular-mocks/angular-mocks.js',
            '../webroot/bower_components/angular-material/angular-material.js',
            //'webroot/components/**/*.js',
            //'webroot/view*/**/*.js',
            
            './unit/**/*.js'
        ],
        
        frameworks: ['jasmine'],
        
        browsers: ['Chrome'],
        
        plugins: [
            'karma-chrome-launcher',
            'karma-firefox-launcher',
            'karma-jasmine',
            'karma-junit-reporter'
        ],
        
        junitReporter: {
            outputFile: 'test_out/unit.xml',
            suite: './unit'
        }

    });
};
