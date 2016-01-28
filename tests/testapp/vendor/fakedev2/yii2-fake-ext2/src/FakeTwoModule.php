<?php

namespace fakedev2\FakeTwo;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application;

class FakeTwoModule extends Module implements BootstrapInterface
{

    public $testConfigName = 'fake-two';
    public $testConfigNumber = 'two';
    public $testConfigDescription = 'second fake ext skeleton';

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        return Yii::$app->getModule('fake-two');
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['fake-two'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
    }


}
