<?php

namespace DevGroup\ExtensionsManager;

use DevGroup\DeferredTasks\commands\DeferredController;
use DevGroup\ExtensionsManager\handlers\DeferredQueueCompleteHandler;
use DevGroup\ExtensionsManager\models\Extension;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;

/**
 * Class ExtensionsManager is the main module in extensions-manager package.
 *
 * @package DevGroup\ExtensionsManager
 */
class ExtensionsManager extends Module implements BootstrapInterface
{
    /**
     * Set of constant listed below describes DeferredGroup. Using them we can define what kind of activity was performed.
     * COMPOSER_INSTALL_DEFERRED_GROUP and COMPOSER_UNINSTALL_DEFERRED_GROUP are used in
     * DeferredQueueCompleteHandler as identifiers to rewrite self::$extensionsStorage file after
     * extension install or uninstall process.
     */
    const COMPOSER_INSTALL_DEFERRED_GROUP = 'ext_manager_composer_install';
    const COMPOSER_UNINSTALL_DEFERRED_GROUP = 'ext_manager_composer_uninstall';
    const EXTENSION_ACTIVATE_DEFERRED_GROUP = 'ext_manager_extension_activate';
    const EXTENSION_DEACTIVATE_DEFERRED_GROUP = 'ext_manager_extension_deactivate';
    const EXTENSION_DUMMY_DEFERRED_GROUP = 'ext_manager_dummy_report';

    /**
     * Next set of constants describes tasks what can be performed from front-end. All of this used as a data attributes
     * in buttons markup and then in the ExtensionsController to define what task we need to do.
     */
    const INSTALL_DEFERRED_TASK = 'install-def-task';
    const UNINSTALL_DEFERRED_TASK = 'uninstall-def-task';
    const ACTIVATE_DEFERRED_TASK = 'activate-def-task';
    const DEACTIVATE_DEFERRED_TASK = 'deactivate-def-task';
    const CHECK_UPDATES_DEFERRED_TASK = 'check-updates-def-task';

    /**
     * Migration directions used in the ExtensionsController
     */
    const MIGRATE_TYPE_DOWN = 'down';
    const MIGRATE_TYPE_UP = 'up';

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

    /** @var string Packegist URL */
    public $packagistUrl = 'https://packagist.org';

    /**
     * @var string token to access api.github.com.
     * Without this token you will able to perform 60 requests daily only
     */
    public $githubAccessToken;

    /**
     * @var string  due to https://developer.github.com/v3/#user-agent-required
     * you have to pass username or application name in headers while requesting github API
     */
    public $applicationName = 'DevGroup-ru/yii2-extensions-manager';

    /** @var string github API URL */
    public $githubApiUrl = 'https://api.github.com';

    /** @var int number of Extension shown on Extension Search and Extension Index pages */
    public $extensionsPerPage = 10;

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
        Event::on(DeferredController::className(),
            DeferredController::EVENT_DEFERRED_QUEUE_COMPLETE,
            [DeferredQueueCompleteHandler::className(), 'handleEvent']
        );
    }

    /**
     * Returns Extension[] or one Extension array by package name found in self::$extensionsStorage.
     *
     * @param string $packageName
     * @param bool $ignoreCache
     * @return Extension|models\Extension[]
     */
    public function getExtensions($packageName = '', $ignoreCache = false)
    {
        if (count($this->extensions) === 0 || true === $ignoreCache) {
            $fileName = Yii::getAlias($this->extensionsStorage);
            if (true === file_exists($fileName) && true === is_readable($fileName)) {
                $this->extensions = include $fileName;
            }
        }
        if (false === empty($packageName) && true === isset($this->extensions[$packageName])) {
            return $this->extensions[$packageName];
        }
        return $this->extensions;
    }

    /**
     * @param string $packageName
     * @return bool
     */
    public function extensionIsActive($packageName)
    {
        $ext = $this->getExtensions($packageName, true);
        if (true === isset($ext['is_active'])) {
            return 1 == $ext['is_active'];
        }
        return false;
    }

    /**
     * @param string $packageName
     * @return bool
     */
    public function extensionIsCore($packageName)
    {
        $ext = $this->getExtensions($packageName);
        if (true === isset($ext['is_core'])) {
            return 1 == $ext['is_core'];
        }
        return false;
    }

    /**
     * @return array
     */
    public static function navLinks()
    {
        return $navItems = [
            'index' => [
                'label' => Yii::t('extensions-manager', 'Extensions'),
                'url' => ['/extensions-manager/extensions/index'],
            ],
            'search' => [
                'label' => Yii::t('extensions-manager', 'Search'),
                'url' => ['/extensions-manager/extensions/search'],
            ],
            'config' => [
                'label' => Yii::t('extensions-manager', 'Configuration'),
                'url' => ['config', 'sectionIndex' => 0],
            ],
        ];
    }
}
