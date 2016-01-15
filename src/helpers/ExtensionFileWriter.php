<?php
namespace DevGroup\ExtensionsManager\helpers;

use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use yii\base\Component;
use Yii;

class ExtensionFileWriter extends Component
{
    /**
     * Calculates differences between @vengor/composer/installed.json and ExtensionsManager::$extensionsStorage
     * and writing new ExtensionsManager::$extensionsStorage
     */
    public static function updateConfig()
    {
        /** @var ExtensionsManager $module */
        $module = Yii::$app->getModule('extensions-manager');
        $extensions = $module->getExtensions();
        $installed = ComposerInstalledSet::get()->getInstalled();
        $fileName = Yii::getAlias($module->extensionsStorage);
        $writer = new ApplicationConfigWriter(['filename' => $fileName]);
        $config = self::rebuldConfig($installed);
        if (false === empty($extensions)) {
            $toAdd = array_udiff($config, $extensions, [self::className(), 'arraysCompareCallback']);
            $toRemove = array_udiff($extensions, $config, [self::className(), 'arraysCompareCallback']);
            foreach ($toRemove as $name => $ext) {
                if (true === isset($extensions[$name])) {
                    unset($extensions[$name]);
                }
            }
            $config = array_merge($extensions, $toAdd);
        }
        $writer->addValues($config);
        $writer->commit();
    }

    /**
     * @param $config
     * @return array
     */
    private static function rebuldConfig($config)
    {
        $config = is_array($config) ? $config : [];
        foreach ($config as $i => $ext) {
            $config[$ext['name']] = [
                'composer_name' => $ext['name'],
                'composer_type' => $ext['type'],
                'is_active' => 0,
                //TODO implement core extensions checking
                'is_core' => 0,
            ];
        }
        return $config;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public static function arraysCompareCallback($a, $b)
    {
        if (true === isset($a['composer_name'], $b['composer_name'])) {
            return strcmp($a['composer_name'], $b['composer_name']);
        }
        return 0;
    }
}