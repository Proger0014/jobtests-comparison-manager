<?php

namespace ComparisonManager\domain\service;

use ComparisonManager\common\abstraction\DateTimeProvider;
use DateTime;

class DefaultDateTimeProvider implements DateTimeProvider
{

    function now(): DateTime
    {
        return new DateTime();
    }
}