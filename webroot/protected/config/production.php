<?php
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
	
	'preload'=>array('log'),
	
	'defaultController' => 'site',
			
	'components'=>array(
	    'request'=>array(
		'enableCsrfValidation' => false,
		'csrfTokenName' => 'token',
		'enableCookieValidation' => true
	    ),
	    'session'=>array(
		'cookieParams'=>array(
		    'httponly'=>true
		),
		'autoStart' => true
	    ),
	    'urlManager'=>array(
		'urlFormat' => 'path',

		'showScriptName' => false,

		'rules' => array(
		    '' => 'site/index',
		    'page/<p:(\w+)>' => 'site/page',
		    'api/<path:.*>' => 'api/call',

		    'sitemap.xml' => 'site/sitemap',

		    array(
			'class' => 'application.components.Routing'
		    )
		)
	    ),
	    'user'=>array(
			'class' => 'PeerbookUser',
			'loginUrl' => array('index'),
	    ),
            'bol' => array(
                'class' => 'ext.bol.BolComponent',
                'siteId' => 0,
                'key' => '',
                'secret' => ''					
            ),
            'google_books' => array(
                'class' => 'ext.google.BookComponent'
            )
	),
    )
);
