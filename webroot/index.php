<?php
define('YII_DEBUG', $_SERVER['HTTP_HOST'] === 'peerbieb.plank.nl');

define('YII_DEBUG_USE_CACHE', false);

// change the following paths if necessary
$yii=dirname(__FILE__).'/../vendor/yiisoft/yii/framework/yiilite.php';
$loader = require_once(dirname(__FILE__).'/../vendor/autoload.php');

if (YII_DEBUG) {
    $config = dirname(__FILE__) . '/protected/config/dev.php';
} else {
    $config = dirname(__FILE__) . '/protected/config/production.php';
}

require_once($yii);
Yii::$classMap = $loader->getClassMap();
Yii::createWebApplication($config)->run();
