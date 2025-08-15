<?php

namespace ComparisonManager\web\assets;

use yii\web\AssetBundle;

/**
 * Main web application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];
    public $js = [
        'js/main.js',
        'https://use.fontawesome.com/releases/v7.0.0/js/all.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
