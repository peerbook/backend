<?php
return CMap::mergeArray(
    require(dirname(__FILE__) . '/main.php'), 
    array(
	'components' => array(
	    'mongodb' => array(
		'db' => 'peerbook_unittest',
		'enableProfiling' => true
	    ),
	),
    )
);
