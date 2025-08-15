<?php

use ComparisonManager\common\abstraction\DateTimeProvider;
use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\domain\service\AddressService;
use ComparisonManager\domain\service\CsvInputStream;
use ComparisonManager\domain\service\DefaultDateTimeProvider;
use ComparisonManager\domain\service\OrganizationService;

return [
    'container' => [
        'singletons' => [
            InputStream::class => CsvInputStream::class,
            DateTimeProvider::class => DefaultDateTimeProvider::class,
            AddressService::class,
            OrganizationService::class
        ]
    ]
];