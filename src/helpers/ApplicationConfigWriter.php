<?php

namespace DevGroup\ExtensionsManager\helpers;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Writer for application configs
 * @package app\modules\config\helpers
 */
class ApplicationConfigWriter extends Component
{
    /**
     * @var string Filename to read-write for
     */
    public $filename = null;
    /**
     * @var array Configuration array that will be written to filesystem
     */
    public $configuration = [];
    /**
     * @var string Code to append
     */
    public $append = '';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->filename === null) {
            throw new InvalidConfigException(
                Yii::t(
                    'extensions-manager',
                    'Filename should be set for ApplicationConfigWriter'
                )
            );
        }
        $this->filename = Yii::getAlias($this->filename);
    }

    /**
     * Add values to current configuration array with merging.
     * @param array $values Values to merge from
     */
    public function addValues($values)
    {
        $this->configuration = ArrayHelper::merge($this->configuration, $values);
    }
    /**
     * Writes all configuration to application configuration file
     * @return bool result, true if success
     */
    public function commit()
    {
        $data = <<<PHP
<?php
/*
 * ! WARNING !
 *
 * This file is auto-generated.
 * Please don't modify it by-hand or all your changes can be lost.
 */
{$this->append}
return
PHP;
        $data .= VarDumper::export($this->configuration);
        $data .= ";\n\n";

        $result = file_put_contents($this->filename, $data, LOCK_EX) !== false;
        if ($result) {
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($this->filename, true);
            }
            if (function_exists('apc_delete_file')) {
                @apc_delete_file($this->filename);
            }
        }
        return $result;
    }
}
