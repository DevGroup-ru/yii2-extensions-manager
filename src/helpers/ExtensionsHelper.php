<?php

namespace DevGroup\ExtensionsManager\helpers;

use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use Yii;
use yii\helpers\ArrayHelper;

class ExtensionsHelper
{
    public static function getConfigurables()
    {
        $installed = ComposerInstalledSet::get()->getInstalled();
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