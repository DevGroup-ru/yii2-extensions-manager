<?php

namespace DevGroup\ExtensionsManager\components;

use DevGroup\ExtensionsManager\models\Extension;
use Yii;
use yii\helpers\Json;

/**
 * Gets and returns array of compatible extensions from composer/installed.json
 *
 * Class ComposerInstalledSet
 * @package DevGroup\ExtensionsManager\components
 */
class ComposerInstalledSet
{
    private $set = [];
    private static $instance;

    /**
     * @inheritdoc
     */
    private function __construct()
    {
        $installed = [];
        $installedJson = Yii::getAlias('@vendor') . '/composer/installed.json';
        if (true === file_exists($installedJson) && is_readable($installedJson)) {
            $installed = Json::decode(file_get_contents($installedJson));
            $compatibleTypes = Extension::getTypes();
            foreach ($installed as $i => $data) {
                if (true === isset($data['name']) && true === isset($compatibleTypes[$data['type']])) {
                    $installed[$data['name']] = $data;
                }
                unset($installed[$i]);
            }
        }
        $this->set = $installed;
    }

    /**
     * @return ComposerInstalledSet
     */
    public static function get()
    {
        if (false === self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getInstalled($name = ''){
        if (false === empty($name)) {
            return true === isset($this->set[$name]) ? $this->set[$name] : [];
        }
        return $this->set;
    }
}