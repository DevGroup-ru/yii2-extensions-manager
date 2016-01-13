<?php

namespace DevGroup\ExtensionsManager\handlers;

use DevGroup\DeferredTasks\events\DeferredQueueCompleteEvent;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\ExtensionsManager\controllers\ExtensionsController;
use DevGroup\ExtensionsManager\helpers\ExtensionFileWriter;
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
            case ExtensionsController::COMPOSER_INSTALL_DEFERRED_GROUP :
            case ExtensionsController::COMPOSER_UNINSTALL_DEFERRED_GROUP :
                file_put_contents('/home/pavel/handle.txt', 'got fine event task' . PHP_EOL, FILE_APPEND);
                ExtensionFileWriter::updateConfig();
                break;
            default :
                return;
        }
    }
}