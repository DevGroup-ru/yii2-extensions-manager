<?php

namespace DevGroup\ExtensionsManager;

use DevGroup\DeferredTasks\events\DeferredQueueEvent;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use DevGroup\DeferredTasks\commands\DeferredController;
use DevGroup\ExtensionsManager\commands\ExtensionController;
use DevGroup\ExtensionsManager\handlers\DeferredQueueCompleteHandler;

class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['extensions-manager'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
        DeferredQueueEvent::on(DeferredController::className(),
            DeferredController::EVENT_DEFERRED_QUEUE_COMPLETE,
            [DeferredQueueCompleteHandler::className(), 'handleEvent']
        );
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap['extension'] = ExtensionController::className();
        }
    }
}