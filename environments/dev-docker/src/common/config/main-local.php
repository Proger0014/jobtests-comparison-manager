<?php

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=db;port=3306;dbname=db',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ]
    ],
];
