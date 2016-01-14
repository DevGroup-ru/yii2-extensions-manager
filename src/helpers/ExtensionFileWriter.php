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
        $config = self::rebuldConfig($installed);
        if (false === empty($extensions)) {
//            $toAdd = array_diff_assoc($config, $extensions);
//            $toRemove = array_diff_assoc($extensions, $config);
//            foreach ($toRemove as $name => $ext) {
//                if (true === isset($extensions[$name])) {
//                    unset($extensions[$name]);
//                }
//            }
//            $config = array_merge($extensions, $toAdd);
        }
        $writer->addValues($config);
        $writer->commit();
    }

    /**
     * @param $config
     * @return array
     */
    private static function rebuldConfig($config){
        foreach ($config as $i => $ext) {
            $config[$ext['name']] = [
                'composer_name' => $ext['name'],
                'composer_type' => $ext['type'],
                'is_active' => 0,
                'is_core' => 0,
            ];
            unset($config[$i]);
        }
        return $config;
    }
}