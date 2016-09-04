<?php

namespace DevGroup\ExtensionsManager\commands;

use DevGroup\DeferredTasks\helpers\DeferredHelper;
use DevGroup\DeferredTasks\helpers\ReportingChain;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\helpers\ExtensionFileWriter;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;

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
        $this->module = ExtensionsManager::module();
        $this->extensions = $this->module->getExtensions();
    }

    /**
     * Starts extension activation process
     *
     * @param string $packageName
     * @param int $runMigrations
     * @return bool|int
     */
    public function actionActivate($packageName, $runMigrations = 1)
    {
        return $this->process($packageName, 1, (int) $runMigrations);
    }

    /**
     * Starts extension deactivation process
     *
     * @param string $packageName
     * @param int $runMigrations
     * @return bool|int
     */
    public function actionDeactivate($packageName, $runMigrations = 1)
    {
        return $this->process($packageName, 0, (int) $runMigrations);
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
     * Updates config.
     * Calculates differences between @vengor/composer/installed.json and ExtensionsManager::$extensionsStorage
     * and writes new ExtensionsManager::$extensionsStorage
     *
     */
    public function actionUpdateConfig()
    {
        $result = (int) (ExtensionFileWriter::updateConfig() !== true);

        if ($result === 0) {
            $this->stdout('Update OK' . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stderr('Error updating extensions storage: ', Console::FG_RED);
            $this->stderr(ExtensionsManager::module()->extensionsStorage . PHP_EOL);
        }

        return $result;
    }

    /**
     * Lists installed extensions
     * @param bool $sort Sort extensions alphabetically
     * @return int
     */
    public function actionList($sort = true)
    {
        $sort = (bool) $sort;
        if ($sort) {
            ksort($this->extensions);
        }
        foreach ($this->extensions as $name => $extension) {
            if ($extension['is_active']) {
                $this->stdout('[active] ', Console::FG_GREEN);
            } else {
                $this->stdout('         ', Console::FG_RED);
            }
            $this->stdout(str_pad($name, 50, ' '));
            $this->stdout(' - ');
            $color = Console::FG_BLACK;
            switch ($extension['composer_type']) {
                case 'yii2-extension':
                    $color = Console::FG_BLUE;
                    break;
                case 'dotplant-extension':
                    $color = Console::FG_GREEN;
                    break;
            }
            $this->stdout($extension['composer_type'] . PHP_EOL, $color);
        }
        return 0;
    }

    /**
     * Activates/Deactivates Extension
     *
     * @param $packageName
     * @param integer $state
     * @param integer $runMigrations
     * @return bool
     */
    private function process($packageName, $state = 1, $runMigrations = 1)
    {
        $actionText = 0 === (int) $state
            ? 'deactivated'
            : 'activated';
        if (true === isset($this->extensions[$packageName]['is_active'])) {
            if ($runMigrations === 1) {
                $chain = new ReportingChain();

                ExtensionDataHelper::prepareMigrationTask(
                    $this->extensions[$packageName],
                    $chain,
                    (int) $state ? ExtensionsManager::MIGRATE_TYPE_UP : ExtensionsManager::MIGRATE_TYPE_DOWN,
                    ExtensionsManager::EXTENSION_DEACTIVATE_DEFERRED_GROUP
                );
                if (null !== $firstTaskId = $chain->registerChain()) {
                    DeferredHelper::runImmediateTask($firstTaskId);
                } else {
                    $this->stderr("Unable to run task chain\n");
                }
            }
            $this->extensions[$packageName]['is_active'] = $state;
            if (true === $this->writeConfig()) {
                $this->stdout(
                    str_replace('{actionText}', $actionText, 'Extension successfully {actionText}.') . PHP_EOL
                );
                //this means successfully termination. Because process starts in command line shell
                return 0;
            }
        } else {
            $this->stdout(
                str_replace('{actionText}', $actionText, 'You trying to {actionText} not existing extension!') . PHP_EOL
            );
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
            $this->stdout('Extensions configuration successfully updated.' . PHP_EOL);
            if (true === $this->module->configurationUpdater->updateConfiguration(false)) {
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
