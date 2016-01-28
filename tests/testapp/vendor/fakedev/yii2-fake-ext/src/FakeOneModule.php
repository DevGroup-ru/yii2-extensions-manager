<?php

namespace fakedev\FakeOne;

use Yii;
use yii\base\BootstrapInterface;use yii\base\Module;
use yii\web\Application;

class FakeOneModule extends Module implements BootstrapInterface
{

    public $testConfigName = 'fake-one';
    public $testConfigNumber = 'one';
    public $testConfigDescription = 'first fake ext skeleton';

    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        return Yii::$app->getModule('fake-one');
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['fake-one'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
    }
}
