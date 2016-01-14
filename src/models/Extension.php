<?php

namespace DevGroup\ExtensionsManager\models;

use Yii;

/**
 * @property string $composer_name
 * @property string $composer_type
 * @property integer $is_active
 * @property integer $is_core
 */
class Extension
{
    const TYPE_DOTPLANT = 'dotplant-extension';
    const TYPE_YII = 'yii2-extension';

    public static function getTypes()
    {
        return [
            self::TYPE_DOTPLANT => Yii::t('extensions-manager', 'Dotplant extension'),
            self::TYPE_YII => Yii::t('extensions-manager', 'Yii2 extension'),
        ];
    }
}
