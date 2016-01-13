<?php
namespace DevGroup\ExtensionsManager\helpers;

use DevGroup\ExtensionsManager\ExtensionsManager;
use yii\base\Component;
use Yii;
use yii\helpers\Json;

class ExtensionFileWriter extends Component
{
    /**
     *
     */
    public static function updateConfig()
    {
        /** @var ExtensionsManager $module */
        $module = Yii::$app->getModule('extensions-manager');
        $config = $installed = [];
        $extensions = $module->getExtensions();
        $installedJson = Yii::getAlias('@vendor').'/composer/installed.json';
        if (true === file_exists($installedJson) && is_readable($installedJson)) {
            $installed = Json::decode(file_get_contents($installedJson));
        }
        $fileName = Yii::getAlias($module->extensionsStorage);
        $writer = new ApplicationConfigWriter(['filename' => $fileName]);
        if (true === empty($extensions)) {
            $config = self::rebuldConfig($installed);
        } else {
        }
        $writer->addValues($config);
        $writer->commit();
    }

    /**
     * @param $config
     * @return array
     */
    private static function rebuldConfig($config){

        return $config;
    }
}