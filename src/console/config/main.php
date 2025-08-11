<?php

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'comparison-manager\console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        '\yii\console\controllers\ServeController' => [
            'class' => \yii\console\controllers\ServeController::class,
            'docroot' => dirname(__DIR__) . '/web/web',
        ]
    ]
];
