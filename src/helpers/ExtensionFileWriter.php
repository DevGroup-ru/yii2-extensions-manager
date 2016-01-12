<?php
namespace DevGroup\ExtensionsManager\helpers;

use yii\base\Component;

class ExtensionFileWriter extends Component
{
    public static function updateConfig($configArray, $filename, $remove = false)
    {
        reset($configArray);
        $key = key($configArray);
        $config = [];
        $configWriter = new ApplicationConfigWriter([
            'filename' => $filename
        ]);
        if (true === file_exists($filename)) {
            $config = include $filename;
        }
        if (true === $remove && true === isset($config[$key])) {
            unset($config[$key]);
        } else {
            $configWriter->configuration = $config;
            $configWriter->addValues($configArray);
        }
        $configWriter->commit();
    }
}