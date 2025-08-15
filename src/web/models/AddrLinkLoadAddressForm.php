<?php

namespace ComparisonManager\web\models;

use yii\base\Model;
use yii\web\UploadedFile;

class AddrLinkLoadAddressForm extends Model
{
    /** @var UploadedFile */
    public $csv = null;
    public int $orgId;

    public function rules()
    {
        return [
            [['csv'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv', 'checkExtensionByMimeType' => false],
            [['orgId'], 'integer'],
        ];
    }

    public function safeAttributes()
    {
        return [
            'csv',
            'orgId',
        ];
    }
}