<?php

namespace DevGroup\ExtensionsManager\models;

use DevGroup\ExtensionsManager\ExtensionsManager;
use Yii;

class ExtensionsConfiguration extends BaseConfigurationModel
{
    public function __construct($config = [])
    {
        $attributes = [
            'extensionsStorage'
        ];

        parent::__construct($attributes, $config);
        $module = Yii::$app->getModule('extensions-manager');
        if ($module === null) {
            $module = new ExtensionsManager('extensions-manager');
        }
        $this->extensionsStorage = $module->extensionsStorage;
    }

    /**
     * Validation rules for this model
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['extensionsStorage', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'extensionsStorage' => Yii::t('extensions-manager', 'Extensions storage'),
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'extensions-manager' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => '@vendor/devgroup/yii2-extensions-manager/src/messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'extension-manager' => [
                    'class' => ExtensionsManager::className(),
                    'extensionsStorage' => $this->extensionsStorage,
                ]
            ],
        ];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function appParams()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     *
     * @return array
     */
    public function aliases()
    {
        return [];
    }
}
