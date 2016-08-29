<?php

namespace fakedev2\FakeTwo\models;

use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use fakedev2\FakeTwo\FakeTwoModule;
use Yii;

class FakeConfiguration extends BaseConfigurationModel
{
    public function getModuleClassName()
    {
        return FakeTwoModule::className();
    }

    /**
     * Validation rules for this model
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['testConfigName', 'testConfigNumber', 'testConfigDescription'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'testConfigName' => Yii::t('fake-two', 'test config name'),
            'testConfigNumber' => Yii::t('fake-two', 'test config number'),
            'testConfigDescription' => Yii::t('fake-two', 'test config description'),
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
                        'fake-two' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'fake-two' => [
                    'class' => FakeTwoModule::className(),
                    'testConfigName' => $this->testConfigName,
                    'testConfigNumber' => $this->testConfigNumber,
                    'testConfigDescription' => $this->testConfigDescription,
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
