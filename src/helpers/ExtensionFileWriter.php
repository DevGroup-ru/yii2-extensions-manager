<?php
namespace DevGroup\ExtensionsManager\helpers;

use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use yii\base\Component;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;

class ExtensionFileWriter extends Component
{
    /**
     * Calculates differences between @vengor/composer/installed.json and ExtensionsManager::$extensionsStorage
     * and writing new ExtensionsManager::$extensionsStorage
     */
    public static function updateConfig()
    {
        /** @var ExtensionsManager $module */
        $module = ExtensionsManager::module();
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
        return $writer->commit();
    }

    /**
     * @return bool
     */
    public static function generateConfig()
    {
        $installed = ComposerInstalledSet::get()->getInstalled();
        $fileName = Yii::getAlias(ExtensionsManager::module()->extensionsStorage);
        $writer = new ApplicationConfigWriter(['filename' => $fileName]);
        $config = self::rebuldConfig($installed);
        $writer->addValues($config);
        return $writer->commit();
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
        //TODO implement checking of broken extension array and extension repair method
        return 0;
    }

    /**
     * Checks and creates if necessary and possible folder to store and local composer.json
     *
     * @param string $dir
     * @param array $data
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public static function checkLocalStorage($dir, $data)
    {
        $fn = $dir . '/composer.json';
        $created = true;
        if (true === FileHelper::createDirectory($dir)) {
            if (false === file_exists($fn)) {
                $created = file_put_contents(
                    $fn,
                    Json::encode($data, JSON_FORCE_OBJECT | 320)
                );
            }
            if (false === $created) {
                Yii::$app->session->setFlash('error',
                    Yii::t('extensions-manager', 'Unable to create local composer.json file')
                );
            }
        } else {
            Yii::$app->session->setFlash('error',
                Yii::t('extensions-manager', 'Unable to create folder to store local composer.json file')
            );
            $created = false;
        }
        return $created;
    }
}