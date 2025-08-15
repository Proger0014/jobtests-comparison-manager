<?php

namespace ComparisonManager\common\models;

use yii\base\Model;

class ProcessingResult extends Model
{
    public int $processed;
    public int $matched;
    public int $auto;
    public int $skipped;
}