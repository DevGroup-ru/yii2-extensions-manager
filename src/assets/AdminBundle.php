<?php

namespace DevGroup\ExtensionsManager\assets;

use Yii;
use yii\helpers\Url;
use yii\web\AssetBundle;

class AdminBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';
        parent::init();
    }

    public $js = [
        'js/ext-manager.js',
    ];

    public $css = [
        'css/ext-manager.css',
    ];

    public static function register($view)
    {
        $bundle = parent::register($view);
        $detailsUrl = Url::to(['/extensions-manager/extensions/details']);
        $runTaskUrl = Url::to(['/extensions-manager/extensions/run-task']);
        $endpointUrl = Url::to(['/extensions-manager/extensions/deferred-report-queue-item']);
        $buttonText = Yii::t('extensions-manager', 'Done');
        $js = <<<JS
    window.ExtensionsManager = window.ExtensionsManager || {};
    window.ExtensionsManager.detailsUrl = '$detailsUrl';
    window.ExtensionsManager.runTaskUrl = '$runTaskUrl';
    window.ExtensionsManager.endpointUrl = '$endpointUrl';
    window.ExtensionsManager.detailsTemplate = '<tr class="extension-info-tr"><td colspan="4">{details}</td></tr>';
    window.ExtensionsManager.buttonText = '$buttonText';
JS;
        $view->registerJs($js, \yii\web\View::POS_HEAD);
        return $bundle;
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'DevGroup\DeferredTasks\assets\AdminBundle',
    ];
}
