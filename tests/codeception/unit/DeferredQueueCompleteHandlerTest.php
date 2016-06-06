<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\DeferredTasks\events\DeferredQueueCompleteEvent;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\DeferredTasks\models\DeferredQueue;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\handlers\DeferredQueueCompleteHandler;
use Symfony\Component\Process\Process;
use testsHelper\TestConfigCleaner;
use yii\console\Application;
use Yii;

class DeferredQueueCompleteHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected static $migrationPath;

    public function setUp()
    {
        $config = include __DIR__ . '/../../testapp/config/console.php';
        new Application($config);
        Yii::$app->cache->flush();
        self::$migrationPath = Yii::getAlias('@vendor') . '/devgroup/yii2-deferred-tasks/src/migrations';
        Yii::$app->runAction('migrate/down', [99999, 'interactive' => 0, 'migrationPath' => self::$migrationPath]);
        Yii::$app->runAction('migrate/up', ['interactive' => 0, 'migrationPath' => self::$migrationPath]);
    }

    public function tearDown()
    {
        Yii::$app->runAction('migrate/down', [99999, 'interactive' => 0, 'migrationPath' => self::$migrationPath]);
        if (Yii::$app && Yii::$app->has('session', true)) {
            Yii::$app->session->close();
        }
        Yii::$app = null;
    }

    public function testHandleCorrectEvent()
    {
        TestConfigCleaner::removeExtFile();
        $this->assertFalse(TestConfigCleaner::checkExtFile());
        Yii::setAlias('@vendor', realpath(__DIR__ . '/../../testapp/vendor'));
        $group = new DeferredGroup();
        $group->loadDefaultValues();
        $group->name = ExtensionsManager::COMPOSER_INSTALL_DEFERRED_GROUP;
        $group->group_notifications = 0;
        $group->save();
        $queue = new DeferredQueue([]);
        $queue->deferred_group_id = $group->id;
        $process = new Process('pwd > /dev/null');
        $process->run();
        $queue->setProcess($process);
        $event = new DeferredQueueCompleteEvent($queue);
        DeferredQueueCompleteHandler::handleEvent($event);
        $this->assertTrue(TestConfigCleaner::checkExtFile());
    }

    public function testHandleWrongGroup()
    {
        TestConfigCleaner::removeExtFile();
        $this->assertFalse(TestConfigCleaner::checkExtFile());
        Yii::setAlias('@vendor', realpath(__DIR__ . '/../../testapp/vendor'));
        $group = new DeferredGroup();
        $group->loadDefaultValues();
        $group->name = ExtensionsManager::ACTIVATE_DEFERRED_TASK;
        $group->group_notifications = 0;
        $group->save();
        $queue = new DeferredQueue([]);
        $queue->deferred_group_id = $group->id;
        $process = new Process('pwd > /dev/null');
        $process->run();
        $queue->setProcess($process);
        $event = new DeferredQueueCompleteEvent($queue);
        DeferredQueueCompleteHandler::handleEvent($event);
        $this->assertFalse(TestConfigCleaner::checkExtFile());
    }

    public function testHandleNullGroup()
    {
        TestConfigCleaner::removeExtFile();
        $this->assertFalse(TestConfigCleaner::checkExtFile());
        Yii::setAlias('@vendor', realpath(__DIR__ . '/../../testapp/vendor'));
        $queue = new DeferredQueue([]);
        $queue->deferred_group_id = 42;
        $process = new Process('pwd > /dev/null');
        $process->run();
        $queue->setProcess($process);
        $event = new DeferredQueueCompleteEvent($queue);
        DeferredQueueCompleteHandler::handleEvent($event);
        $this->assertFalse(TestConfigCleaner::checkExtFile());
    }

    public function testHandleBadQueue()
    {
        TestConfigCleaner::removeExtFile();
        $this->assertFalse(TestConfigCleaner::checkExtFile());
        Yii::setAlias('@vendor', realpath(__DIR__ . '/../../testapp/vendor'));
        $group = new DeferredGroup();
        $group->loadDefaultValues();
        $group->name = ExtensionsManager::ACTIVATE_DEFERRED_TASK;
        $group->group_notifications = 0;
        $group->save();
        $queue = new DeferredQueue([]);
        $queue->deferred_group_id = $group->id;
        $process = new Process('pwd > /dev/null');
        $process->run();
        $queue->setProcess($process);
        $queue->exit_code = 1;
        $event = new DeferredQueueCompleteEvent($queue);
        DeferredQueueCompleteHandler::handleEvent($event);
        $this->assertFalse(TestConfigCleaner::checkExtFile());
    }

}
