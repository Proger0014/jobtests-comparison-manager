<?php

namespace ComparisonManager\common\models;

use DateTime;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property-read int $id
 * @property ?int $organization_id
 * @property ?string $address
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
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
                    'created_at' => AttributeTypecastBehavior::TYPE_STRING,
                    'updated_at' => AttributeTypecastBehavior::TYPE_STRING
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => false
            ]
        ];
    }

    public function getCreatedAt(): DateTime {
        return DateTime::createFromFormat('Y-m-d H:i:s', $this->created_at);
    }

    public function setCreatedAt(DateTime $value) {
        $this->created_at = $value->format('Y-m-d H:i:s');
    }

    public function getUpdatedAt(): DateTime {
        return DateTime::createFromFormat('Y-m-d H:i:s', $this->updated_at);
    }

    public function setUpdatedAt(DateTime $value) {
        $this->updated_at = $value->format('Y-m-d H:i:s');
    }

    public function getOrganization(): ActiveQuery {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function getAddressRef(): ActiveQuery {
        return $this->hasOne(AddressRef::class, ['src_id' => 'id']);
    }
}