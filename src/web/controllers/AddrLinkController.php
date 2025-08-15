<?php

namespace ComparisonManager\web\controllers;

use ComparisonManager\common\models\AddressSrc;
use ComparisonManager\common\models\Page;
use ComparisonManager\domain\service\AddressService;
use ComparisonManager\domain\service\OrganizationService;
use ComparisonManager\web\models\AddrLinkGridViewModel;
use ComparisonManager\web\models\AddrLinkIndexModel;
use ComparisonManager\web\models\AddrLinkLoadAddressForm;
use ComparisonManager\web\models\BindsInfo;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

class AddrLinkController extends Controller
{
    private AddressService $addressService;
    private OrganizationService $organizationService;

    public function __construct(
        $id,
        $module,
        AddressService $addressService,
        OrganizationService  $organizationService,
        $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->addressService = $addressService;
        $this->organizationService = $organizationService;
    }

    public function actionIndex(): string
    {
        $model = new AddrLinkIndexModel();
        $model->attributes = $this->request->queryParams;
        $model->organizations = $this->organizationService->getAll();

        $srcAddresses = $this->addressService->getSrcPage(
            $model->orgId ?? 0,
            $model->page ?? 1,
            $model->pageSize ?? 100);

        $mappedArr = array_map(function (AddressSrc $item) {
            $existsRef = $item->getAddressRef()->one();

            $attributes = [
                'src' => $item
            ];

            if ($existsRef) {
                $attributes['ref'] = $existsRef;
            }

            return new AddrLinkGridViewModel($attributes);
        }, $srcAddresses->entities);

        $model->gridModel = new Page([
            'entities' => $mappedArr,
            'total' => $srcAddresses->total
        ]);

        return $this->render('index', ['model' => $model]);
    }

    public function actionSearchRef(int $orgId, int $page, int $pageSize, string $q = ''): Response {
        $refs = $this->addressService->getRefPage($q, $orgId, $page, $pageSize);

        return $this->asJson($refs);
    }

    public function actionBind(): Response {
        $srcId = $this->request->post('srcId');
        $refId = $this->request->get('id');

        $bindRef = $this->addressService->bind($srcId, $refId);

        return $this->asJson($bindRef);
    }

    public function actionUnbind(): Response {
        $refId = $this->request->get('id');

        $this->addressService->unbind($refId);

        return $this->asJson(null);
    }

    public function actionLoadAddress(): Response {

        if ($this->request->isPost) {
            $form = new AddrLinkLoadAddressForm();
            $form->load($this->request->post());
            $form->csv = UploadedFile::getInstance($form, 'csv');
            if ($form->validate('csv')) {
                $this->addressService->loadRefs($form->orgId, $form->csv->tempName);
            } else {
                Yii::debug($form->getErrors());
            }
        }

        return $this->redirect($this->request->referrer);
    }

    public function actionAutoRun(): Response {
        $orgId = $this->request->post('orgId');
        $threshold = $this->request->post('threshold');
        $rebindManual = $this->request->post('rebindManual');

        $result = $this->addressService->compareAndAutoInstallAddresses($orgId, $threshold, $rebindManual);

        return $this->asJson($result);
    }

    public function actionFindManualBindsInfo(): Response {
        $orgId = $this->request->post('orgId');

        $count = $this->addressService->findManualBindsCount($orgId);

        $response = new BindsInfo([
            'count' => $count,
        ]);

        return $this->asJson($response);
    }
}