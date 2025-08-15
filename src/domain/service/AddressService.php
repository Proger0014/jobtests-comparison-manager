<?php

namespace ComparisonManager\domain\service;

use ComparisonManager\common\abstraction\DateTimeProvider;
use ComparisonManager\common\enum\MatchType;
use ComparisonManager\common\models\AddressRef;
use ComparisonManager\common\models\AddressSrc;
use ComparisonManager\common\models\Page;
use Yii;
use yii\db\Exception;

class AddressService
{
    private DateTimeProvider $dateTimeProvider;

    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public function getRefPage(string $addressSearch, int $orgId, int $page, int $pageSize): Page
    {

        $entities = AddressRef::findBySql("SELECT * FROM `addresses_ref` 
                                            WHERE organization_id = :orgId AND address LIKE :addressSearch
                                            ORDER BY MATCH (address) AGAINST (:addressSearch) DESC")
            ->offset((max($page, 1) - 1) * $pageSize)
            ->limit($pageSize)
            ->params(['addressSearch' => '%' . $addressSearch . '%', 'orgId' => $orgId])
            ->all();

        $total = AddressRef::find()
            ->where(['organization_id' => $orgId])
            ->count();

        return new Page([
            'entities' => $entities,
            'total' => $total
        ]);
    }

    public function getSrcPage(int $orgId, int $page, int $pageSize): Page {
        $entities = AddressSrc::find()
            ->where(['organization_id' => $orgId])
            ->offset((max($page, 1) - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        $total = AddressSrc::find()
            ->where(['organization_id' => $orgId])
            ->count();

        return new Page([
            'entities' => $entities,
            'total' => $total
        ]);
    }

    public function bind(int $srcId, int $refId): ?AddressRef {
        $transaction = Yii::$app->db->beginTransaction();

        Yii::debug("Биндинг $refId в $srcId", __METHOD__);

        try {
            $addressRef = AddressRef::findOne(['id' => $refId]);
            $addressRef->match_score = null;
            $addressRef->match_type = MatchType::manual()->getType();
            $addressRef->src_id = $srcId;
            $addressRef->setUpdatedAt($this->dateTimeProvider->now());
            $addressRef->save();

            $transaction->commit();

            Yii::debug("Успех биндинга $refId в $srcId", __METHOD__);

            return $addressRef;
        } catch (Exception $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::error("Ошибка биндинга $refId в $srcId", __METHOD__);
            $transaction->rollBack();
        }

        return null;
    }

    public function unbind(int $refId): bool {
        $transaction = Yii::$app->db->beginTransaction();

        Yii::debug("Отвязка $refId", __METHOD__);

        try {
            $addressRef = AddressRef::findOne(['id' => $refId]);
            $addressRef->match_type = MatchType::unmatched()->getType();
            $addressRef->src_id = null;
            $addressRef->match_score = null;
            $addressRef->setUpdatedAt($this->dateTimeProvider->now());

            $addressRef->save();

            $transaction->commit();

            Yii::debug("Успех отвязки биндинга $refId", __METHOD__);

            return true;
        } catch (Exception $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::error("Ошибка отвязки биндинга $refId", __METHOD__);
            $transaction->rollBack();
        }

        return false;
    }
}