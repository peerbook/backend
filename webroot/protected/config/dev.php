<?php
return CMap::mergeArray(
    require(dirname(__FILE__).'/production.php'),
    array(
	'components'=>array(

	    'mongodb' => array(
		'server' => 'mongodb://127.0.0.1:27017',
		'db' => 'peerbook_dev',
		'enableProfiling' => true
	    ),

	    'log'=>array(
		'class'=>'CLogRouter',
		'routes'=>array(
		    array(
			'class' => 'CFileLogRoute',
			'levels' => 'Error, Warning, Debug, Trace',
		    ),
		    //array('class'=>'CProfileLogRoute'),
		),
	    ), 
	    'urlManager' => array(
		'showScriptName'=>false
	    )
	),
        'params' => array(
            'cdn' => 'http://peerbook.dev'
        )
    )
);
