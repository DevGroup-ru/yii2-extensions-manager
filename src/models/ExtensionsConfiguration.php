<?php

namespace DevGroup\ExtensionsManager\models;

use DevGroup\ExtensionsManager\controllers\ExtensionsController;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\commands\ExtensionController;
use Yii;

/**
 * Class ExtensionsConfiguration
 *
 * @package DevGroup\ExtensionsManager\models
 * @codeCoverageIgnore
 */
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
            'extensionsPerPage',
            'composerPath',
            'verbose',
        ];

        parent::__construct($attributes, $config);
        $module = ExtensionsManager::module();
        $this->extensionsStorage = $module->extensionsStorage;
        $this->packagistUrl = $module->packagistUrl;
        $this->githubAccessToken = $module->githubAccessToken;
        $this->githubApiUrl = $module->githubApiUrl;
        $this->applicationName = $module->applicationName;
        $this->extensionsPerPage = $module->extensionsPerPage;
        $this->composerPath = $module->composerPath;
        $this->verbose = $module->verbose;
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
                [
                    'extensionsStorage',
                    'packagistUrl',
                    'githubAccessToken',
                    'applicationName',
                    'githubApiUrl',
                    'composerPath'
                ],
                'string',
                'max' => 180
            ],
            [['extensionsPerPage', 'verbose'], 'integer'],
            [['packagistUrl', 'applicationName'], 'required'],
            [['extensionsStorage', 'packagistUrl', 'githubAccessToken', 'applicationName', 'githubApiUrl'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'packagistUrl' => Yii::t('extensions-manager', 'Packagist URL'),
            'githubAccessToken' => Yii::t('extensions-manager', 'Github API access token'),
            'applicationName' => Yii::t('extensions-manager', 'Github application name'),
            'githubApiUrl' => Yii::t('extensions-manager', 'Github API URL'),
            'extensionsStorage' => Yii::t('extensions-manager', 'Extensions storage'),
            'extensionsPerPage' => Yii::t('extensions-manager', 'Extensions per page'),
            'composerPath' => Yii::t('extensions-manager', 'Path to Composer'),
            'verbose' => Yii::t('extensions-manager', 'Verbose output'),
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
                    'extension' => ExtensionController::class,
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
                            'basePath' => __DIR__ . '/messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'extensions-manager' => [
                    'class' => ExtensionsManager::class,
                    'extensionsStorage' => $this->extensionsStorage,
                    'packagistUrl' => $this->packagistUrl,
                    'githubAccessToken' => $this->githubAccessToken,
                    'applicationName' => $this->applicationName,
                    'githubApiUrl' => $this->githubApiUrl,
                    'extensionsPerPage' => $this->extensionsPerPage,
                    'composerPath' => $this->composerPath,
                    'verbose' => $this->verbose,
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
