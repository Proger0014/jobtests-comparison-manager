<?php

use ComparisonManager\common\abstraction\DateTimeProvider;
use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\domain\service\CsvInputStream;
use ComparisonManager\domain\service\DefaultDateTimeProvider;

return [
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'container' => [
        'singletons' => [
            InputStream::class => CsvInputStream::class,
            DateTimeProvider::class => DefaultDateTimeProvider::class
        ]
    ],

    'params' => [
        'csv' => dirname(__DIR__) . '/default.csv',
    ]
];
