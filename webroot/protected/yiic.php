<?php

$config = dirname(__FILE__).'/config/console.php';

// change the following paths if necessary
$loader = require_once(dirname(__FILE__).'/../../vendor/autoload.php');

require_once(dirname(__FILE__).'/../../vendor/yiisoft/yii/framework/yii.php');
Yii::$classMap = $loader->getClassMap();

$yiic = dirname(__FILE__).'/../../vendor/yiisoft/yii/framework/yiic.php';
require_once($yiic);



