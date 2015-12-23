<?php

namespace DevGroup\ExtensionsManager;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {

            if (isset($app->params['yii.migrations'])) {
                $vendorsInstalledFile = Yii::getAlias('@vendor/composer/installed.json');
                $installed = Json::decode(file_get_contents($vendorsInstalledFile));

                foreach ($installed as $package) {
                    $packagePath = '@vendor' . DIRECTORY_SEPARATOR . $package['name'] . DIRECTORY_SEPARATOR;
                    $packageMigrations = (array) ArrayHelper::getValue($package, 'extra.migrationPath', []);
                    foreach ($packageMigrations as $migrationPath) {
                        $app->params['yii.migrations'][] = $packagePath . $migrationPath;
                    }

                }
            }

        }
    }
}