<?php

namespace  DevGroup\ExtensionsManager\assets;

use yii\web\AssetBundle;

class AdminBundle extends AssetBundle
{
    public $js = [
        'js/ext-manager.js',
    ];

    public $css = [
        'styles/ext-manager.css',
    ];

    public function init()
    {
        parent::init();
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'dist/';
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'DevGroup\DeferredTasks\assets\AdminBundle',
    ];
}
