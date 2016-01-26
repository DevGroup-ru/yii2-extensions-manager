<?php
namespace DevGroup\ExtensionsManager\commands;

use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
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
     * @return int
     */
    public function actionDummy($message)
    {
        $this->stdout($message . PHP_EOL);
        return 0;
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
                ? 'deactivated'
                : 'activated';
            $this->extensions[$packageName]['is_active'] = $state;
            if (true === $this->writeConfig()) {
                $this->stdout(str_replace('{actionText}', $actionText, 'Extension successfully {actionText}.') . PHP_EOL);
                //this means successfully termination. Because process starts in command line shell
                return 0;
            }
        } else {
            $this->stdout('You trying to activate not existing extension!' . PHP_EOL);
        }
        return 1;
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

        if (true === $writer->commit()) {
            $this->module->getExtensions();
            $this->stdout('Extensions configuration successfully updated.' . PHP_EOL);
            if (true === $this->module->configurationUpdater->updateConfiguration(false, false)) {
                $this->stdout('Application configuration successfully updated.' . PHP_EOL);
                return true;
            } else {
                $this->stdout('Application configuration update error.' . PHP_EOL);
                return false;
            }
        } else {
            $this->stdout('There was an error while updating extensions configuration file.' . PHP_EOL);
        }
        return false;
    }
}