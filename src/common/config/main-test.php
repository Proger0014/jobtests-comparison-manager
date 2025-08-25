<?php

$params = require(__DIR__ . '/params-test.php');

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$params['db']['host']};port={$params['db']['port']};dbname={$params['db']['db']}",
            'username' => $params['db']['user'],
            'password' => $params['db']['password'],
            'charset' => 'utf8',
        ]
    ],
    'params' => $params
];
