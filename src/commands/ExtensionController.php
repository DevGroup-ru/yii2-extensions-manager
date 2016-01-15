<?php
namespace DevGroup\ExtensionsManager\commands;

use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use yii\console\Controller;
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
     * Activates Extension
     * @param $packageName
     * @param $statusCode
     * @return bool|int
     */
    public function actionMarkActive($packageName, $statusCode)
    {
        if (0 == $statusCode) {
            if (true === isset($this->extensions[$packageName]['is_active'])) {
                $this->extensions[$packageName]['is_active'] = 1;
                $this->writeConfig();
            } else {
                return $this->stdout(Yii::t('extensions-manager', 'You trying to activate not existing extension!') . PHP_EOL);
            }
        } else {
            return $this->stdout(
                Yii::t('extensions-manager', 'If you see this, migrations fails and extension could not be activated.') . PHP_EOL
            );
        }
    }

    /**
     * Deactivates Extension
     * @param $packageName
     * @param $statusCode
     * @return bool|int
     */
    public function actionMarkInactive($packageName, $statusCode)
    {
        if (0 == $statusCode) {
            if (true === isset($this->extensions[$packageName]['is_active'])) {
                $this->extensions[$packageName]['is_active'] = 0;
                $this->writeConfig();
            } else {
                return $this->stdout(Yii::t('extensions-manager', 'You trying to deactivate not existing extension!') . PHP_EOL);
            }
        } else {
            return $this->stdout(
                Yii::t('extensions-manager', 'If you see this, migrations fails and extension could not be activated.') . PHP_EOL
            );
        }
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
        $writer->commit();
    }
}