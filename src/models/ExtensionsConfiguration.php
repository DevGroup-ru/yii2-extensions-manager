<?php

namespace DevGroup\ExtensionsManager\models;

use DevGroup\ExtensionsManager\controllers\ExtensionsController;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\commands\ExtensionController;
use Yii;

class ExtensionsConfiguration extends BaseConfigurationModel
{
    public function __construct($config = [])
    {
        $attributes = [
            'extensionsStorage',
            'packagistUrl',
            'githubAccessToken',
            'applicationName',
            'githubApiUrl',
            'extensionsPerPage'
        ];

        parent::__construct($attributes, $config);
        $module = Yii::$app->getModule('extensions-manager');
        if ($module === null) {
            $module = new ExtensionsManager('extensions-manager');
        }
        $this->extensionsStorage = $module->extensionsStorage;
        $this->packagistUrl = $module->packagistUrl;
        $this->githubAccessToken = $module->githubAccessToken;
        $this->githubApiUrl = $module->githubApiUrl;
        $this->applicationName = $module->applicationName;
        $this->extensionsPerPage = $module->extensionsPerPage;
    }

    /**
     * Validation rules for this model
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['extensionsStorage', 'packagistUrl', 'githubAccessToken', 'applicationName', 'githubApiUrl'],
                'string',
                'max' => 180
            ],
            [['extensionsPerPage'], 'integer'],
            [['packagistUrl', 'applicationName'], 'required'],
            [['extensionsStorage', 'packagistUrl', 'githubAccessToken', 'applicationName', 'githubApiUrl'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'packagistUrl' => Yii::t('extensions-manager', 'Packagist URL'),
            'githubAccessToken' => Yii::t('extensions-manager', 'Hithub API access token'),
            'applicationName' => Yii::t('extensions-manager', 'Github application name'),
            'githubApiUrl' => Yii::t('extensions-manager', 'Hithub API URL'),
            'extensionsStorage' => Yii::t('extensions-manager', 'Extensions storage'),
            'extensionsPerPage' => Yii::t('extensions-manager', 'Extensions per page'),
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
        return
            [
                'controllerMap' => [
                    'extension' => ExtensionController::className(),
                ]
            ];
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
                'extensions-manager' => [
                    'class' => ExtensionsManager::className(),
                    'extensionsStorage' => $this->extensionsStorage,
                    'packagistUrl' => $this->packagistUrl,
                    'githubAccessToken' => $this->githubAccessToken,
                    'applicationName' => $this->applicationName,
                    'githubApiUrl' => $this->githubApiUrl,
                    'extensionsPerPage' => $this->extensionsPerPage,
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
