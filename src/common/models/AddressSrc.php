<?php

namespace ComparisonManager\common\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class AddressSrc extends ActiveRecord
{
    /**
     * @return array<string>
     */
    public static function primaryKey(): array {
        return ['id'];
    }

    public static function tableName(): string {
        return '{{addresses_src}}';
    }

    /**
     * @return array<string>
     */
    public function attributes(): array
    {
        return [
            'id',
            'organization_id',
            'address',
            'created_at',
            'updated_at'
        ];
    }

    public function behaviors(): array
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'organization_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'address' => AttributeTypecastBehavior::TYPE_STRING,
                    'created_at' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'updated_at' => AttributeTypecastBehavior::TYPE_INTEGER
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => false
            ]
        ];
    }

    public function getOrganization(): ActiveQuery {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function getAddressesRef(): ActiveQuery {
        return $this->hasMany(AddressRef::class, ['src_id' => 'id']);
    }
}