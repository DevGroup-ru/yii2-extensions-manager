<?php
namespace DevGroup\ExtensionsManager\commands;

use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use DevGroup\ExtensionsManager\helpers\ConfigurationUpdater;
use yii\console\Controller;
use Symfony\Component\Process\ProcessBuilder;
use Yii;

class ExtensionController extends Controller
{
    /** @var  ExtensionsManager */
    public $module;
    /** @var  array */
    public $extensions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->module = Yii::$app->getModule('extensions-manager');
        $this->extensions = $this->module->getExtensions();
    }

    /**
     * Starts extension activation process
     *
     * @param string $packageName
     * @return bool|int
     */
    public function actionActivate($packageName)
    {
        return self::process($packageName, 1);
    }

    /**
     * Starts extension deactivation process
     *
     * @param string $packageName
     * @return bool|int
     */
    public function actionDeactivate($packageName)
    {
        return self::process($packageName, 0);
    }

    /**
     * Shows given message in reporting window
     *
     * @param string $message
     */
    public function actionDummy($message)
    {
        $this->stdout($message . PHP_EOL);
        return;
    }

    /**
     * Activates/Deactivates Extension
     *
     * @param $packageName
     * @param integer $state
     * @return bool
     */
    private function process($packageName, $state = 1)
    {
        if (true === isset($this->extensions[$packageName]['is_active'])) {
            $actionText = 0 == $state
                ? Yii::t('extensions-manager', 'deactivated')
                : Yii::t('extensions-manager', 'activated');
            $this->extensions[$packageName]['is_active'] = $state;
            if (true === $this->writeConfig()) {
                $this->stdout(Yii::t('extensions-manager', 'Extension successfully {actionText}.', [
                        'actionText' => $actionText,
                    ]) . PHP_EOL);
                return true;
            } else {
                $this->stdout(Yii::t('extensions-manager', 'Unable to write config file.') . PHP_EOL);
            }
        } else {
            $this->stdout(Yii::t('extensions-manager', 'You trying to activate not existing extension!') . PHP_EOL);
        }
        return false;
    }

    /**
     * Writes updated extensions file
     */
    private function writeConfig()
    {
        $fileName = Yii::getAlias($this->module->extensionsStorage);
        $writer = new ApplicationConfigWriter([
            'filename' => $fileName,
        ]);
        $writer->addValues($this->extensions);
        if (true === (new ConfigurationUpdater())->updateConfiguration(false)) {
            $this->stdout(Yii::t('extensions-manager', 'Application configuration successfully updated.') . PHP_EOL);
            return $writer->commit();
        } else {
            $this->stdout(Yii::t('extensions-manager', 'Application configuration update error.') . PHP_EOL);
        }
        return false;
    }
}