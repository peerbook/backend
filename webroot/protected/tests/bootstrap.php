<?php
$config = dirname(__FILE__).'/../../protected/config/unittest.php';

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once(dirname(__FILE__).'/../../../vendor/autoload.php');
require_once(dirname(__FILE__).'/../../../vendor/yiisoft/yii/framework/yiilite.php');
Yii::import('system.test.*');
require_once(dirname(__FILE__).'/MongoFixtures.php');
require_once(dirname(__FILE__).'/MongoTestCase.php');

Yii::createWebApplication($config);