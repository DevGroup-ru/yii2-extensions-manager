<?php

namespace DevGroup\ExtensionsManager\handlers;

use DevGroup\DeferredTasks\events\DeferredQueueCompleteEvent;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\ExtensionsManager\helpers\ExtensionFileWriter;
use DevGroup\ExtensionsManager\ExtensionsManager;
use yii\base\Object;

class DeferredQueueCompleteHandler extends Object
{
    /**
     * @param DeferredQueueCompleteEvent $event
     */
    public static function handleEvent($event)
    {
        //else we have unfinished task
        if ($event->queue->exit_code != 0) {
            return;
        }
        /** @var DeferredGroup $group */
        if (null === $group = DeferredGroup::loadModel($event->queue->deferred_group_id)) {
            return;
        }
        switch ($group->name) {
            case ExtensionsManager::COMPOSER_INSTALL_DEFERRED_GROUP :
            case ExtensionsManager::COMPOSER_UNINSTALL_DEFERRED_GROUP :
                ExtensionFileWriter::updateConfig();
                break;
            default :
                return;
        }
    }
}