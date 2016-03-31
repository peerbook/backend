<?php

return CMap::mergeArray(
    require(dirname(__FILE__) . '/main.php'), 
    array(
	'commandMap' => array(
	    'migratemongo' => array(
		'class' => 'EMigrateMongoCommand'
	    )
	),
	'components' => array(
	),
    )
);
