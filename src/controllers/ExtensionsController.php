<?php
namespace DevGroup\ExtensionsManager\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\ExtensionsManager\actions\ConfigurationIndex;
use DevGroup\ExtensionsManager\ExtensionsManager;
use Packagist\Api\Client;
use yii\data\ArrayDataProvider;
use DevGroup\ExtensionsManager\models\Extension;
use Yii;

class ExtensionsController extends BaseController
{
    private $packagistUrl;

    public function init()
    {
        parent::init();
        $this->packagistUrl = static::module()->packagistUrl;
    }

    public function actions()
    {
        return [
            'config' => [
                'class' => ConfigurationIndex::className(),
            ],
        ];
    }

    public function actionIndex()
    {

    }

    public function actionSearch($sort = '', $type = Extension::TYPE_DOTPLANT, $query = '')
    {
        $packagist = new Client();
        $packagist->setPackagistUrl($this->packagistUrl);
        $filters = ['type' => $type];
        if (1 === preg_match('{([\\\\/])}', $query, $m)) {
            $filters['vendor'] = array_shift(explode($m[0], $query));
        }
        $packages = $packagist->search($query, $filters);
        return $this->renderResponse(
            'search',
            [
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $packages,
                    'pagination' => [
                        'pageSize' => 10,
                    ],
                ]),
            ]
        );
    }

    private static function doRequest()
    {

    }

    /**
     * @param $repo
     * @return bool
     */
    public static function isGit($repo)
    {
        return false !== strpos('github', $repo);
    }

    public function actionDetails()
    {

    }

    public function actionInstall()
    {

    }

    /**
     * @return null| ExtensionsManager
     */
    public static function module()
    {
        return Yii::$app->getModule('extensions-manager');
    }
}