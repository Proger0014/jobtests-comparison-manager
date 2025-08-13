<?php

namespace ComparisonManager\common\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property-read int $id
 * @property string $name
 */
class Organization extends ActiveRecord
{
    /**
     * @return array<string>
     */
    public static function primaryKey(): array {
        return ['id'];
    }

    public static function tableName(): string {
        return '{{organizations}}';
    }

    /**
     * @return array<string>
     */
    public function attributes(): array
    {
        return [
            'id',
            'name'
        ];
    }

    public function behaviors(): array
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'name' => AttributeTypecastBehavior::TYPE_STRING
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => false
            ]
        ];
    }

    public function getAddressesSrc(): ActiveQuery {
        return $this->hasMany(AddressSrc::class, ['organization_id' => 'id']);
    }

    public function getAddressesRef(): ActiveQuery {
        return $this->hasMany(AddressRef::class, ['organization_id' => 'id']);
    }
}