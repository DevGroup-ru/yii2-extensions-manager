<?php

namespace DevGroup\ExtensionsManager\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class ExtensionsHelper
{
    public static function getConfigurables()
    {
        $vendorsInstalledFile = Yii::getAlias('@vendor/composer/installed.json');
        $installed = Json::decode(file_get_contents($vendorsInstalledFile));

        $configurables = [];
        foreach ($installed as $package) {
            $packageConfigurablesFile = ArrayHelper::getValue($package, 'extra.configurables', null);
            if ($packageConfigurablesFile === null) {
                continue;
            }

            $fn = Yii::getAlias('@vendor')
                . DIRECTORY_SEPARATOR
                . $package['name']
                . DIRECTORY_SEPARATOR
                . $packageConfigurablesFile;

            if (file_exists($fn) && is_readable($fn)) {
                $packageConfigurables = include($fn);
                array_walk($packageConfigurables, function(&$item) use($package) {
                    $item['package'] = $package['name'];
                    $translationCategory = ArrayHelper::getValue($package, 'extra.translationCategory', 'app');
                    $item['sectionNameTranslated'] = Yii::t($translationCategory, $item['sectionName']);
                });
                $configurables = ArrayHelper::merge($configurables, $packageConfigurables);
            }
        }

        return $configurables;
    }
}