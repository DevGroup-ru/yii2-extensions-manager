<?php

namespace DevGroup\ExtensionsManager;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Module;

/**
 * Class ExtensionsManager is the main module in extensions-manager package.
 *
 * @package DevGroup\ExtensionsManager
 */
class ExtensionsManager extends Module implements BootstrapInterface
{
    /**
     * ConfigurationUpdater component is used for writing application configs.
     * You can configure it as usual yii2 Component.
     *
     * @var \DevGroup\ExtensionsManager\helpers\ConfigurationUpdater
     */
    public $configurationUpdater = [
        'class' => 'DevGroup\ExtensionsManager\helpers\ConfigurationUpdater',
        'configs' => [
            'common-generated' => 'commonApplicationAttributes',
            'web-generated' => 'webApplicationAttributes',
            'console-generated' => 'consoleApplicationAttributes',
            'params-generated' => 'appParams',
            'aliases-generated' => 'aliases',
        ],
    ];

    /**
     * Location of extensions storage file. Aliases can be used.
     * File contains of a php return statement with array consisting elements with the following information:
     * - composer_name - name of composer package
     * - composer_type - type of composer package(if is set in composer.json of package)
     * - is_active - boolean, if this extension is active
     * - is_core - boolean, true if it is core extension and should be protected from uninstalling
     *
     * @var string
     */
    public $extensionsStorage = '@app/config/extensions.php';

    /**
     * @var array Array of extensions descriptions
     */
    private $extensions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->configurationUpdater = Yii::createObject($this->configurationUpdater);

    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['extensions-manager'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];

    }

    /**
     * @return array Returns array of extensions
     */
    public function getExtensions()
    {
        if (count($this->extensions) === 0) {
            $this->extensions = include(Yii::getAlias($this->extensionsStorage));
        }
        return $this->extensions;
    }
}
