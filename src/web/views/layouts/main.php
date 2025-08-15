<?php

use ComparisonManager\web\assets\AppAsset;
use kartik\icons\FontAwesomeAsset;
use kartik\select2\Select2KrajeeAsset;
use yii\helpers\Html;

/** @var $this yii\web\View */
/** @var $content string */

AppAsset::register($this);
FontAwesomeAsset::register($this);
Select2KrajeeAsset::register($this);
?>

<?php $this->beginPage(); ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?php $this->registerCsrfMetaTags(); ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody(); ?>

    <?= $content ?>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>