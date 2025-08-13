<?php

namespace ComparisonManager\common\abstraction;

use DateTime;

interface DateTimeProvider
{
    function now(): DateTime;
}