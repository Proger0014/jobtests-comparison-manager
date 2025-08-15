<?php

use ComparisonManager\common\enum\MatchType;
use ComparisonManager\common\models\Organization;
use ComparisonManager\common\util\MatchTypeUtil;
use ComparisonManager\web\models\AddrLinkGridViewModel;
use ComparisonManager\web\models\AddrLinkIndexModel;
use ComparisonManager\web\models\AddrLinkLoadAddressForm;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Button;
use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Modal;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;


/** @var $this View */
/** @var $model AddrLinkIndexModel */

$this->title = 'Home';

$data = array_map(fn (Organization $item) => [$item->id => $item->name], $model->organizations)[0];
?>

<div class="container">
    <div class="panel mt-5">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <label for="organization_select" class="pe-3"><b>Организация:</b> </label>
                <?= Select2::widget([
                        'id' => 'organization_select',
                        'name' => 'organizations',
                        'data' => $data
                ]); ?>

                <?php Modal::begin([
                        'id' => 'address_ref_load_dialog',
                        'toggleButton' => [
                                'label' => 'Загрузить адреса',
                                'class' => 'ms-2 btn btn-primary',
                        ],
                        'title' => 'Загрузка адресов',

                ]) ?>
                    <p>Выберите csv файл ваших адресов:</p>

                    <?php
                    $modelForm = new AddrLinkLoadAddressForm();

                    $activeForm = ActiveForm::begin([
                            'action' => Url::to(['addr-link/load-address']),
                            'method' => 'post',
                            'options' => [
                                'enctype' => 'multipart/form-data'
                            ]
                    ]) ?>

                        <?= $activeForm->field($modelForm, 'csv')->fileInput()->label('Файл'); ?>

                        <?= $activeForm->field($modelForm, 'orgId')->hiddenInput(['value' => $model->orgId])->label(false); ?>

                        <?= Button::widget([
                                'options' => [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary',
                                ]
                        ]) ?>

                    <?php ActiveForm::end() ?>
                <?php Modal::end(); ?>
            </div>
        </div>
        <div class="d-flex align-items-center mt-4">
            <p class="m-0 pe-3">Выбрать совпадения ниже: </p>
            <div>
                <?= MaskedInput::widget([
                        'name' => 'occurrence',
                        'id' => 'occurrence',
                        'options' => [
                                'class' => 'form-control me-2',
                                'style' => 'width: 70px;'
                        ],
                        'mask' => '[100%]|[99%]',
                ]) ?>
            </div>
            <?= Button::widget([
                    'label' => 'Применить',
                    'options' => [
                            'class' => 'btn-primary',
                            'id' => 'occurrence_submit'
                    ],
            ]); ?>
        </div>
    </div>

    <div class="goal mt-5">
        <?php Pjax::begin([
                'options' => [
                        'id' => 'pjax-default',
                ]
        ]); ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $model->gridModel->entities,
                'pagination' => false,
            ]),
            'columns' => [
                [
                    'class' => yii\grid\SerialColumn::class,
                    'header' => '№'
                ],
                [
                    'attribute' => 'src.address',
                    'label' => 'Наш адрес'
                ],
                [
                    'attribute' => 'ref.address',
                    'label' => 'Адрес клиента',
                    'content' => function (AddrLinkGridViewModel $item) {
                        /** @var $this View */

                        $conId = "{$item->src->id}_address_ref";
                        $srcId = $item->src->id;

                        if ($item->ref != null) {
                            $text = $item->ref->address;
                            $text = "\"{$text}\"";
                            $id = $item->ref->id;


                            $this->registerJs("selectAddressClient('#$conId', $text, $id)");
                        }

                        $this->registerJs("unbindButtonAction('#$conId')");
                        $this->registerJs("bindButtonAction($srcId, '#$conId')");

                        return Select2::widget([
                            'name' => 'address_ref',
                            'id' => $conId,
                            'options' => ['placeholder' => 'Выберите адрес дома'],
                            'pluginOptions' => [
                                'ajax' => [
                                    'url' => 'search-ref',
                                    'dataType' => 'json',
                                    'data' => new JsExpression('(params) => ajax(params)'),
                                    'processResults' => new JsExpression('(data, params) => ajaxProcessResult(data, params)')
                                ],
                                'templateResult' => new JsExpression('(result) => templateResult(result)'),
                                'templateSelection' => new JsExpression('(result) => templateSelection(result)'),
                                'allowClear' => true,
                            ]
                        ]);
                    }
                ],
                [
                    'attribute' => 'ref.match_type',
                    'label' => 'Тип сопоставления',
                    'content' => function (AddrLinkGridViewModel $item) {
                        return $item->ref == null
                            ? MatchTypeUtil::getTypesLocalization()[MatchType::unmatched()->getType()]
                            : sprintf(MatchTypeUtil::getTypesLocalization()[$item->ref->match_type], $item->ref->match_score);
                    }
                ]
            ]
        ]); ?>

        <?php Pjax::end(); ?>


        <nav class="d-flex justify-content-center">
            <?= LinkPager::widget([
                'pagination' => new Pagination([
                    'pageSizeParam' => 'pageSize',
                    'totalCount' => $model->gridModel->total,
                    'pageSizeLimit' => [100, 100],
                ]),
                'firstPageLabel' => 'Первая',
                'lastPageLabel' => 'Последняя',
            ]); ?>
        </nav>
    </div>
</div>