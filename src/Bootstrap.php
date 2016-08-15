<?php

namespace DevGroup\ExtensionsManager;

use DevGroup\DeferredTasks\events\DeferredQueueEvent;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\models\Extension;
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
            $app->on(Application::EVENT_BEFORE_ACTION, function () {
                $module = ExtensionsManager::module();
                if ($module->autoDiscoverMigrations === true) {
                    if (isset(Yii::$app->params['yii.migrations']) === false) {
                        Yii::$app->params['yii.migrations'] = [];
                    }
                    /** @var array $extensions */
                    $extensions = $module->getExtensions();
                    foreach ($extensions as $name => $ext) {
                        if (
                            $ext['composer_type'] === Extension::TYPE_DOTPLANT
                            && $module->discoverDotPlantMigrations === false
                        ) {
                            continue;
                        }
                        $extData = ComposerInstalledSet::get()->getInstalled($ext['composer_name']);
                        $packageMigrations = ExtensionDataHelper::getInstalledExtraData(
                            $extData,
                            'migrationPath',
                            true
                        );
                        $packagePath = '@vendor' . DIRECTORY_SEPARATOR . $ext['composer_name'];
                        foreach ($packageMigrations as $migrationPath) {
                            Yii::$app->params['yii.migrations'][] = "$packagePath/$migrationPath";
                        }

                    }
                }
            });
        }
    }
}