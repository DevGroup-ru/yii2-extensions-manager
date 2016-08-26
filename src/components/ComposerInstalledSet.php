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
    private $_set = [];
    private static $_instance;

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
        $this->_set = $installed;
    }

    /**
     * @param bool $force whether getting new instance.
     * @return ComposerInstalledSet
     */
    public static function get($force = false)
    {
        if (false === self::$_instance instanceof self || $force === true) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getInstalled($name = '')
    {
        if (false === empty($name)) {
            return true === isset($this->_set[$name]) ? $this->_set[$name] : [];
        }
        return $this->_set;
    }
}
