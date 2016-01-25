<?php

namespace DevGroup\ExtensionsManager\models;

use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\StringHelper;

/**
 * Abstract class for configurable models of configurable modules.
 * @package app\models
 */
abstract class BaseConfigurationModel extends DynamicModel
{

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    abstract public function webApplicationAttributes();

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    abstract public function consoleApplicationAttributes();

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    abstract public function commonApplicationAttributes();

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    abstract public function appParams();

    /**
     * Returns array of aliases that should be set in common config
     * @return array
     */
    abstract public function aliases();

    /**
     * The name of event that is triggered when this configuration is being saved.
     * The event will be triggered before model validation proceeds and after model is loaded with user-input.
     *
     * @return string Configuration save event name
     */
    public function configurationSaveEvent()
    {
        return StringHelper::basename(get_class($this)) . 'ConfigurationSaveEvent';
    }

    /**
     * @return string Returns filename of file for saving model state without '.php' extension
     */
    protected function stateFilename()
    {
        return preg_replace('#^.*\\\\(.*)$#', "$1", $this->className());
    }

    /**
     * Loads state from file
     * @param string $statePath Path where model state files are stored.
     * @return bool result
     */
    public function loadState($statePath)
    {
        $filename = Yii::getAlias($statePath . $this->stateFilename() . '.php');
        if (is_readable($filename) === true) {
            $this->loadAttributesFromState(
                include($filename)
            );
            return true;
        } else {
            return false;
        }
    }

    public function deleteFromState($statePath)
    {
        $filename = Yii::getAlias($statePath . $this->stateFilename() . '.php');
        if (true === is_readable($filename)) {
            return unlink($filename);
        }
        return false;
    }
    /**
     * Saves state to file
     * @param string $statePath Path where model state files are stored.
     * @return bool
     */
    public function saveState($statePath)
    {
        $filename = Yii::getAlias($statePath . $this->stateFilename() . '.php');
        $writer = new ApplicationConfigWriter([
            'filename' => $filename,
        ]);
        $writer->configuration = $this->getAttributesForStateSaving();
        $result =  $writer->commit();
        if (ini_get('opcache.enable')) {
            // invalidate opcache of this files!
            opcache_invalidate(
                Yii::getAlias($filename),
                true
            );
        }
        return $result;
    }

    /**
     * @return array attributes that used for saving configurable model state
     */
    public function getAttributesForStateSaving()
    {
        return $this->getAttributes();
    }

    /**
     * Fills model attributes with previous saved state
     * @param $values array of state form file
     * @return bool result
     */
    public function loadAttributesFromState($values)
    {
        parent::setAttributes($values, false);
        return true;
    }
}
