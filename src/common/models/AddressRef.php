<?php

namespace ComparisonManager\common\models;

use ComparisonManager\common\enum\MatchType;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class AddressRef extends ActiveRecord
{
    /**
     * @return array<string>
     */
    public static function primaryKey(): array {
        return ['id'];
    }

    public static function tableName(): string {
        return '{{addresses_ref}}';
    }

    /**
     * @return array<string>
     */
    public function attributes(): array {
        return [
            'id',
            'organization_id',
            'address',
            'src_id',
            'match_type',
            'match_score',
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
                    'src_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'match_type' => AttributeTypecastBehavior::TYPE_STRING,
                    'match_score' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'updated_at' => AttributeTypecastBehavior::TYPE_INTEGER
                ]
            ]
        ];
    }

    public function getMatchTypeAsEnum(): MatchType {
        return MatchType::from($this->getAttribute('match_type'));
    }

    public function getAddressSrc(): ActiveQuery {
        return $this->hasOne(AddressSrc::class, ['id' => 'src_id']);
    }
}