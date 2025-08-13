<?php

use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\domain\service\CsvInputStream;

return [
    'container' => [
        'singletons' => [
            InputStream::class => CsvInputStream::class,
        ]
    ]
];