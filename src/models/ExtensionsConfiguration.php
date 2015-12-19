<?php

namespace DevGroup\ExtensionsManager\models;

use Yii;

class ExtensionsConfiguration extends BaseConfigurationModel
{
    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes ()
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
    public function consoleApplicationAttributes ()
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
    public function commonApplicationAttributes ()
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
        ];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function appParams ()
    {
        // TODO: Implement appParams() method.
    }

    /**
     * Returns array of aliases that should be set in common config
     *
     * @return array
     */
    public function aliases ()
    {
        // TODO: Implement aliases() method.
}}