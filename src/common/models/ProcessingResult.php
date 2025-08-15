<?php

namespace ComparisonManager\common\models;

use yii\base\Model;

class ProcessingResult extends Model
{
    public int $processed = 0;
    public int $matched = 0;
    public int $auto = 0;
    public int $skipped = 0;
}