<?php

namespace DevGroup\ExtensionsManager\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\ExtensionsManager\actions\ConfigurationIndex;
use Yii;

class ManageConfigurationController extends BaseController
{
    public function actions()
    {
        return [
            'index' => [
                'class' => ConfigurationIndex::className(),
            ],
        ];
    }
}
