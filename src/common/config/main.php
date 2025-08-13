<?php

use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\domain\service\CsvInputStream;

return [
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'container' => [
        'singletons' => [
            InputStream::class => CsvInputStream::class,
        ]
    ],

    'params' => [
        'csv' => dirname(__DIR__) . '/default.csv',
    ]
];
