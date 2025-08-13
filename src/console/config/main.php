<?php

use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\domain\service\CsvInputStream;

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'ComparisonManager\console\commands',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ]
    ]
];
