<?php

namespace DevGroup\ExtensionsManager\helpers;

use DevGroup\ExtensionsManager\components\ConfigurationSaveEvent;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use Yii;
use yii\base\Component;

/**
 * Class ConfigurationUpdater deals with saving configuration.
 * It is used by ConfigController and by extensions on their install/update stage for saving initial values.
 *
 * @package app\modules\config\helpers
 */
class ConfigurationUpdater extends Component
{
    /**
     * BaseConfigurationModel states(all attributes) are saved in separated files.
     * This is a path to folder where to place this states files.
     * Yii2 aliases can be used.
     * @var string
     */
    public $configurablesStatePath = '@app/config/configurables-state';

    /**
     * Path to store generated configs.
     *
     * @var string
     */
    public $generatedConfigsPath = '@app/config/generated';

    /**
     * Array of configs where:
     * - key is filename without '.php' extension
     * - value is name of function in BaseConfigurationModel for retrieving values
     *
     * @var array
     */
    public $configs = [];

    /**
     * Array of configurables sections.
     *
     * @var array
     */
    private $configurables = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->configurablesStatePath = rtrim($this->configurablesStatePath, '/') . '/';
        $this->generatedConfigsPath = rtrim($this->generatedConfigsPath, '/') . '/';
    }

    /**
     * Returns configurables sections.
     * Loads them from files if not loaded.
     * @param bool $ignoreCache
     */
    protected function getConfigurables($ignoreCache = false)
    {
        if (count($this->configurables) === 0 || true === $ignoreCache) {
            $this->configurables = ExtensionsHelper::getConfigurables();
        }
    }

    /**
     * Updates all application configurations
     *
     * @param bool $usePostData Should we populate all configurationModels with user-data
     * @param bool $loadCurrent if true current states will be loaded and merged with newly created model
     * otherwise configs will be just overwritten with data loaded from post if $usePostData === true
     * @return bool true if all is ok
     * @throws \yii\base\InvalidConfigException
     */
    public function updateConfiguration($usePostData = true, $loadCurrent = true)
    {
        /** @var ApplicationConfigWriter[] $configWriters */
        $configWriters = [];
        foreach ($this->configs as $filename => $functionName) {
            $file = $this->generatedConfigsPath . $filename . '.php';
            $configWriters[$filename] = new ApplicationConfigWriter([
                'filename' => $file,
            ]);
            if (true === $loadCurrent) {
                $configWriters[$filename]->addValues(self::loadCurrentConfig($file));
            }
        }
        $this->getConfigurables(true);
        $isValid = true;
        $errorSection = '';

        foreach ($this->configurables as $configurable) {
            if (!isset($configurable['configurationModel'])) {
                continue;
            }
            /** @var BaseConfigurationModel $configurationModel */
            $configurationModel = new $configurable['configurationModel'];
            /** @var ExtensionsManager $manager */
            $manager = Yii::$app->getModule('extensions-manager');
            if (false === $manager->extensionIsActive($configurable['package'])) {
                $configurationModel->deleteFromState($this->configurablesStatePath);
                continue;
            } else {
                $configurationModel->loadState($this->configurablesStatePath);
            }
            $dataOk = true;
            if ($usePostData === true) {
                $dataOk = $configurationModel->load(Yii::$app->request->post());
            }
            if ($dataOk === true) {
                $event = new ConfigurationSaveEvent();
                $event->configurable = &$configurable;
                $event->configurationModel = &$configurationModel;
                $configurationModel->trigger($configurationModel->configurationSaveEvent(), $event);
                if ($event->isValid === true) {
                    if ($configurationModel->validate() === true) {
                        foreach ($configWriters as $filename => $writer) {
                            $callbackFunction = $this->configs[$filename];
                            $writer->addValues(call_user_func([$configurationModel, $callbackFunction]));
                        }
                        $configurationModel->saveState($this->configurablesStatePath);
                    } else {
                        if (Yii::$app->get('session', false)) {
                            Yii::$app->get('session')->setFlash(
                                'info',
                                'Validation error:' . var_export($configurationModel, true)
                            );
                        }
                        $isValid = false;
                    }
                } else {
                    $isValid = false;
                }
                if ($isValid === false) {
                    $errorSection = $configurable['sectionName'];
                    // event is valid, stop saving data
                    break;
                }
            } // model load from user input
        }  // /foreach

        if ($isValid === true) {
            // save all configurations
            $isValid = true;
            foreach ($configWriters as $writer) {
                $isValid = $isValid && $writer->commit();
                if (ini_get('opcache.enable')) {
                    opcache_invalidate(
                        Yii::getAlias($writer->filename),
                        true
                    );
                }
            }
        }

        if (Yii::$app->get('session', false)) {
            if ($isValid === true) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t(
                        'app',
                        'Configuration saved'
                    )
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    Yii::t(
                        'app',
                        'Error saving configuration for module {module}',
                        [
                            'module' => $errorSection,
                        ]
                    )
                );
            }
        }
        return $isValid;
    }

    /**
     * Loads and returns current config state
     * @param string $filename
     * @return array
     */
    public static function loadCurrentConfig($filename)
    {
        $filename = Yii::getAlias($filename);
        $currentConfig = [];
        if (true === file_exists($filename) && true === is_readable($filename)) {
            $currentConfig = include $filename;
        }
        return $currentConfig;
    }


}
