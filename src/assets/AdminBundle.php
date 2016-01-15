<?php

namespace DevGroup\ExtensionsManager\assets;

use yii\helpers\Url;
use yii\web\AssetBundle;

class AdminBundle extends AssetBundle
{
    public $sourcePath = '@vendor/devgroup/yii2-extensions-manager/src/assets/dist';

    public $js = [
        'js/ext-manager.js',
    ];

    public $css = [
        'styles/ext-manager.css',
    ];

    public static function register($view)
    {
        $bundle = parent::register($view);
        $detailsUrl = Url::to(['/extensions-manager/extensions/details']);
        $runTaskUrl = Url::to(['/extensions-manager/extensions/run-task']);
        $endpointUrl = Url::to(['/extensions-manager/extensions/deferred-report-queue-item']);
        $js = <<<JS
    window.ExtensionsManager = window.ExtensionsManager || {};
    window.ExtensionsManager.detailsUrl = '$detailsUrl';
    window.ExtensionsManager.runTaskUrl = '$runTaskUrl';
    window.ExtensionsManager.endpointUrl = '$endpointUrl';
    window.ExtensionsManager.detailsTemplate = '<tr class="extension-info-tr"><td colspan="4">{details}</td></tr>';
JS;
        $view->registerJs($js, \yii\web\View::POS_HEAD);
        return $bundle;
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'DevGroup\DeferredTasks\assets\AdminBundle',
    ];
}
