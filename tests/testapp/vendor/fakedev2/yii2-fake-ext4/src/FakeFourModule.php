<?php

namespace fakedev2\FakeFour;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application;

class FakeFourModule extends Module implements BootstrapInterface
{

    public $testConfigName = 'fake-four';
    public $testConfigNumber = 'four';
    public $testConfigDescription = 'fourth fake ext skeleton';

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        return Yii::$app->getModule('fake-four');
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['fake-four'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
    }


}
