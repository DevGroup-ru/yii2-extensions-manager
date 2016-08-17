<?php

namespace DevGroup\ExtensionsManager;

class Installer
{
    public static function createComposerFile($event)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        $sourceFile = 'vendor/devgroup/yii2-extensions-manager/composer-example.json';
        $targetFile = 'extensions/composer.json';
        if (isset($params[__METHOD__]) && is_array($params[__METHOD__])) {
            if (isset($params[__METHOD__]['sourceFile'])) {
                $sourceFile = $params[__METHOD__]['sourceFile'];
            }
            if (isset($params[__METHOD__]['targetFile'])) {
                $targetFile = $params[__METHOD__]['targetFile'];
            }
        }
        if (file_exists($targetFile) === false) {
            copy($sourceFile, $targetFile);
        }
    }
}
