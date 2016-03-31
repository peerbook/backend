<?php
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Peerbook',
    'language' => 'nl',
    'preload' => ['init'],
    'modules' => array(
		
    ),
    // application components
    'components' => array(
        'mongodb' => array(
            'class' => 'EMongoClient',
            'server' => 'mongodb://localhost:27017',
            'db' => 'peerbieb',
            'options' => array(
                'username' => 'peerbook',
                'password' => ''
            ),
        ),
        'errorHandler' => array(
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
        'init' => ['class' => 'Preload'],
        'cache' => array(
            'class' => 'system.caching.CFileCache',
            'behaviors' => array(
                'helper' => array(
                    'class' => 'CacheBehavior'
                ),
            ),
        ),
    ),
    'params' => array(
		'api_keys' => [],
		'version' => '1.0.0',      
        'cdn' => 'http://peerbook.dev',   
        'android_key' => []
    )
);
