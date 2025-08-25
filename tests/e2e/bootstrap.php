<?php

use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;

defined('YII_BASE_PATH') or define('YII_BASE_PATH', dirname(__DIR__, 2));

require YII_BASE_PATH . '/vendor/autoload.php';
require YII_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php';


$selenium = (new GenericContainer("selenium/standalone-chrome:4.35.0-20250808"))
    ->withLabels(['--shm-size="2g"'])
    ->withExposedPorts('4444:4444')
    ->start();

Yii::$app->params['containers'] = [];
Yii::$app->params['containers']['selenium'] = $selenium;

register_shutdown_function(function () {
    /** @var StartedGenericContainer $selenium */
    $selenium = Yii::$app->params['containers']['selenium'];
});