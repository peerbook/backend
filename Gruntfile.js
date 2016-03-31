module.exports = function (grunt) {

    var cachekey = grunt.option('cachekey') || 'base';

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-copy');
    
    grunt.loadNpmTasks('grunt-karma');
    grunt.loadNpmTasks('grunt-shell'); 

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            scripts: {
                options: {
                    separator: ';'
                },
                dest: 'webroot/dist/packed/app.js',
                src: [
                    'webroot/bower_components/angular/angular.js',
                    'webroot/bower_components/angular-animate/angular-animate.js',
                    'webroot/bower_components/angular-aria/angular-aria.js',
                    'webroot/bower_components/angular-material/angular-material.js',
                    'webroot/bower_components/angular-route/angular-route.js',
                    
                    'webroot/bower_components/angulartics/src/angulartics.js',
                    'webroot/bower_components/angulartics/src/angulartics-ga.js',
                    
                    'webroot/bower_components/webfontloader/webfontloader.js',
                    
                    'webroot/js/**/*.js'
                ]
            }
        },
        jshint: {
            files: ['Gruntfile.js', 'webroot/js/**/*.js'],
            options: {}
        },
        uglify: {
            options: {
                mangle: true,
                sourceMap: true
            },
            build: {
                files: {
                    'webroot/dist/min/peerbieb.min.js': ['webroot/dist/packed/**/*.js']
                }
            }
        },
        less: {
            build: {
                options: {
                    paths: ["webroot/less"],
                    compress : true
                },
                files: {
                    "webroot/css/app.css": "webroot/less/app.less"
                }
            }
        },
        karma: {
            unit: {
                configFile: './test/karma.conf.js',
                autoWatch: false,
                singleRun: true
            }      
        },
        
        shell: {
            options: {
                stderr: false
            },
            protractor: {
                command: [
                    'webdriver-manager start',
                    'protractor test/protractor.conf.js'
                ].join(' | ')
            }
        },
        
        copy: {
            deploy: {
                files: [
                    {expand: true, cwd: 'webroot/dist/min/', src: ['**'], dest: 'webroot/static/' + cachekey + '/js'},
                    {expand: true, cwd: 'webroot/js/', src: ['**'], dest: 'webroot/static/' + cachekey + '/js'},
                    {expand: true, cwd: 'webroot/css/', src: ['**'], dest: 'webroot/static/' + cachekey + '/css'},
                    {expand: true, cwd: 'webroot/images/', src: ['**'], dest: 'webroot/static/' + cachekey + '/images'}
                ]
            }
        },
        
        watch: {
            assets: {
                files: ['webroot/less/**/*.css', 'webroot/js/**/*.js'],
                tasks: ['build']
            }
        }
    });
    
    grunt.registerTask('test:e2e', ['shell:protractor']);
    grunt.registerTask('test:unit', ['karma:unit']);
    grunt.registerTask('test', ['test:unit', 'test:e2e']);

    grunt.registerTask('build', ['less:build', 'concat:scripts', 'uglify:build', 'copy:deploy']);

    grunt.registerTask('deploy', ['build', 'copy:deploy']);

    grunt.registerTask('default', ['build']);
};
