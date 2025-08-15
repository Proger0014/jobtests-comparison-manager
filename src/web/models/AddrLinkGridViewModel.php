<?php

namespace ComparisonManager\web\models;

use ComparisonManager\common\models\AddressRef;
use ComparisonManager\common\models\AddressSrc;
use yii\base\Model;

class AddrLinkGridViewModel extends Model
{
    public AddressSrc $src;
    public ?AddressRef $ref = null;
}