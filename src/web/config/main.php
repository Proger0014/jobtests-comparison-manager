<?php

$config = [
    'id' => 'app-web',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'ComparisonManager\web\controllers',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'defaultRoute' => 'addr-link/redirect',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-web',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'addr-link/<id:\d+>/bind' => 'addr-link/bind',
                'addr-link/<id:\d+>/unbind' => 'addr-link/unbind',
            ]
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
    ],
    'params' => [
        'bsVersion' => '5.x'
    ]
];

if (YII_DEBUG) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module'
    ];

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
}

return $config;