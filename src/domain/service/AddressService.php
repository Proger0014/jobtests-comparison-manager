<?php

namespace ComparisonManager\domain\service;

use ComparisonManager\common\abstraction\DateTimeProvider;
use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\common\enum\MatchType;
use ComparisonManager\common\models\AddressRef;
use ComparisonManager\common\models\AddressSrc;
use ComparisonManager\common\models\Page;
use ComparisonManager\common\models\ProcessingResult;
use Yii;
use yii\db\Exception;

class AddressService
{
    private DateTimeProvider $dateTimeProvider;
    private InputStream $inputStream;

    public function __construct(
        DateTimeProvider $dateTimeProvider,
        InputStream $inputStream)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->inputStream = $inputStream;
    }

    public function getRefPage(string $addressSearch, int $orgId, int $page, int $pageSize): Page
    {

        $entities = AddressRef::findBySql("SELECT * FROM `addresses_ref` 
                                            WHERE organization_id = :orgId AND address LIKE :addressSearch AND src_id IS NULL
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

    public function loadRefs(int $orgId, string $filePath) {
        Yii::debug("Начало загрузки адресов для $orgId из $filePath", __METHOD__);

        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            $this->inputStream->load($filePath, function ($line) use ($orgId) {
                $address = $line[0];

                $addressRef = new AddressRef();
                $addressRef->organization_id = $orgId;
                $addressRef->address = $address;
                $addressRef->match_type = MatchType::unmatched()->getType();
                $addressRef->setUpdatedAt($this->dateTimeProvider->now());

                $addressRef->save();
            });

            $transaction->commit();
            Yii::debug("Адреса для $orgId из $filePath были успешно загружены", __METHOD__);
        } catch (Exception $ex) {
            Yii::error($ex->getMessage(), __METHOD__);
            Yii::error("Не удалось загрузить адреса для $orgId из $filePath", __METHOD__);
            $transaction->rollBack();
        }
    }

    public function findManualBindsCount(int $orgId): int {
        return AddressRef::find()
            ->where(['organization_id' => $orgId])
            ->andWhere(['match_type' => MatchType::manual()->getType()])
            ->count();
    }

    public function compareAndAutoInstallAddresses(int $orgId, int $threshold, bool $rebindManual): ProcessingResult {
        $batchSize = 500;
        $result = new ProcessingResult();

        $inputStr = implode(', ', [
            'orgId => ' . $orgId,
            'threshold => ' . $threshold,
            'rebindManual => ' . $rebindManual,
            'batchSize => ' . $batchSize,
        ]);

        Yii::debug("Начало автосопоставления адресов, аргументы: " . $inputStr, __METHOD__);

        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            $batchQuery = AddressRef::find()
                ->where(['organization_id' => $orgId])
                ->where("(src_id IS NULL AND match_type != :matchType)")
                ->params(['matchType' => MatchType::manual()->getType()]);

            if (!$rebindManual) {
                $batchQuery
                    ->andWhere('!=', ['match_type', MatchType::manual()->getType()]);
            }

            $batchQuery = $batchQuery->batch($batchSize);

            foreach ($batchQuery as $addressRefBatch) {
                $this->compareAndAutoInstallBatchHandler($addressRefBatch, $orgId, $threshold, $result);
            }

            $transaction->commit();

            Yii::debug("Автосопоставление адресов было успешно выполнено, аргументы: " . $inputStr, __METHOD__);
        } catch (Exception $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::debug("Неудалось выполнить автосопоставление адресов, аргументы: " . $inputStr, __METHOD__);
            $transaction->rollBack();
        }

        return $result;
    }

    private function compareAndAutoInstallBatchHandler(array $batch, int $orgId, int $threshold, ProcessingResult $result): void {
        $inputStr = implode(', ', [
            'orgId => ' . $orgId,
            'threshold => ' . $threshold,
            'batchSize => ' . count($batch),
        ]);

        Yii::debug("Сопоставление батча, аргументы: " . $inputStr, __METHOD__);

        $result->processed += count($batch);

        /** @var AddressRef $item */
        foreach ($batch as $item) {
            $refAddress = self::normalizeAddress($item->address);

            if ($threshold < 100) {
                $addressSrcCandidates = AddressSrc::findBySql("SELECT * 
                                           FROM `addresses_src`
                                           WHERE MATCH(address) AGAINST (:refAddress)")
                    ->limit(10)
                    ->params(['refAddress' => $refAddress])
                    ->all();

                $candidates = [];

                foreach ($addressSrcCandidates as $srcItem) {
                    $srcNormalized = self::normalizeAddress($srcItem->address);
                    $refAddressLength = mb_strlen($refAddress);
                    $levenshtein = levenshtein($refAddress, $srcNormalized);

                    $min = min($levenshtein, $refAddressLength);
                    $max = max($levenshtein, $refAddressLength);

                    $score = ($min / $max) * 100;

                    if ($score >= $threshold) {
                        $candidates[] = [
                            'src_id' => $srcItem->id,
                            'score' => $score,
                        ];
                    }
                }

                if (count($addressSrcCandidates) > 0) {
                    $result->matched++;
                }

                usort($candidates, function ($a, $b) {
                    return $b['score'] - $a['score'];
                });

                if (count($candidates) > 1) {
                    $first = $candidates[0];
                    $second = $candidates[1];

                    if (($first['score'] + 5) >= $second['score']) {

                        $addressSrc = [
                            'entity' => array_values(array_filter($addressSrcCandidates, fn ($it) => $it->id == $first['src_id']))[0],
                            'score' => $first['score']
                        ];
                    }
                } else if (count($candidates) == 1) {
                    $first = $candidates[0];

                    $addressSrc = [
                        'entity' => array_values(array_filter($addressSrcCandidates, fn ($it) => $it->id == $first['src_id']))[0],
                        'score' => $first['score']
                    ];
                }
            } else {
                $addressSrc = [
                    'entity' => AddressSrc::findOne(['address' => $refAddress]),
                    'score' => 100
                ];
            }

            if (!empty($addressSrc) && isset($addressSrc['entity'])) {
                $entity = $addressSrc['entity'];

                Yii::debug($addressSrc, __METHOD__);

                /** @var $entity AddressSrc */
                if ($entity->getAddressRef()->one() == null) {
                    $result->auto++;

                    $item->src_id = $addressSrc['entity']->id;
                    $item->match_type = MatchType::auto()->getType();
                    $item->match_score = $addressSrc['score'];
                    $item->setUpdatedAt($this->dateTimeProvider->now());
                    $item->save();
                }
            } else {
                $result->skipped++;
            }
        }
    }

    private static function normalizeAddress(string $address): string {
        $target = mb_strtolower($address);

        $target = mb_ereg_replace('\.', '', $target);
        $target = trim($target);
        $targetArray = explode(' ', $target);
        $newArr = [];

        for ($i = 0; $i < count($targetArray); $i++) {
            $token = $targetArray[$i];

            if (array_key_exists($token, self::$mapReplacement)) {
                $replacement = self::$mapReplacement[$token];

                if (in_array($replacement, self::$removeSpace)) {
                    $nextToken = $targetArray[$i+1];

                    $replacement .= $nextToken;

                    $i++;
                }

                $newArr[] = $replacement;
            } else {
                $newArr[] = $token;
            }
        }

        return implode(' ', $newArr);
    }

    private static array $removeSpace = ['д', 'к'];

    private static array $mapReplacement = [
        'улица' => 'ул',
        'проспект' => 'пр-кт',
        'дом' => 'д',
        'корпус' => 'к',
        'строение' => 'стр'
    ];
}