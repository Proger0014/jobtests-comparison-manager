<?php

namespace ComparisonManager\console\commands;

use ComparisonManager\common\abstraction\DateTimeProvider;
use ComparisonManager\common\abstraction\io\InputStream;
use ComparisonManager\common\models\AddressSrc;
use ComparisonManager\common\models\Organization;
use Yii;
use yii\base\ErrorException;
use yii\console\Controller;

class SeederController extends Controller
{
    const ORG_NAME = 'Технология и Сервис';

    private InputStream $inputStream;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(
        $id,
        $module,
        InputStream $inputStream,
        DateTimeProvider $dateTimeProvider,
        $config = []) {
        parent::__construct($id, $module, $config);

        $this->inputStream = $inputStream;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public function actionSeed(): int {
        Yii::debug("Начало процесса инициализации данныб бд...", __METHOD__);

        $transaction = Yii::$app->getDb()->beginTransaction();

        $organizationHolder = [];

        try {
            $isAdded = $this->seedOrganizations($organizationHolder);

            if (!$isAdded) {
                Yii::debug("Инициализация отменена, данные в базе уже есть", __METHOD__);
                $transaction->rollBack();
                return 0;
            }

            $this->seedAddressesSrc($organizationHolder[0]);
            $transaction->commit();
            Yii::debug("Инициализация базы произошла успешно!", __METHOD__);
        } catch (ErrorException $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $transaction->rollBack();
        }

        return 0;
    }


    private function seedOrganizations(array &$organizationHolder): bool {
        $existsOrganization = Organization::findOne(['name' => self::ORG_NAME]);

        if ($existsOrganization != null) return false;

        $organization = new Organization();
        $organization->name = self::ORG_NAME;
        $organization->save();

        $organizationHolder[] = $organization;

        return true;
    }

    private function seedAddressesSrc(Organization $organization) {
        $this->inputStream->load(\Yii::$app->params['csv'], function (array $line) use ($organization) {
            $addressSrc = new AddressSrc();
            $addressSrc->organization_id = $organization->id;
            $addressSrc->address = $line[0];
            $addressSrc->setCreatedAt($this->dateTimeProvider->now());
            $addressSrc->setUpdatedAt($this->dateTimeProvider->now());

            $addressSrc->save();
        });
    }
}