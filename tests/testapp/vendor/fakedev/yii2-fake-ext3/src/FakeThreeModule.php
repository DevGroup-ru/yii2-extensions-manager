<?php

namespace fakedev\FakeThree;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application;

class FakeThreeModule extends Module implements BootstrapInterface
{

    public $testConfigName = 'fake-three';
    public $testConfigNumber = 'three';
    public $testConfigDescription = 'third fake ext skeleton';

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        return Yii::$app->getModule('fake-three');
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['fake-three'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
    }
}
