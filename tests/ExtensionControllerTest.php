<?php
namespace DevGroup\ExtensionsManager\tests;

use yii\console\Application;
use Yii;

class ExtensionControllerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = include 'config/testapp.php';
        $app = new Application($config);
        Yii::$app->cache->flush();
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        if (Yii::$app && Yii::$app->has('session', true)) {
            Yii::$app->session->close();
        }
        Yii::$app = null;
    }

    public function testActionActivateNonExistingExtension()
    {
        $this->assertEquals(1, Yii::$app->runAction('extension/activate', ['package/extension1']));
    }

    public function testActionDeactivateNonExistingExtension()
    {
        $this->assertEquals(1, Yii::$app->runAction('extension/deactivate', ['package/extension1']));
    }

    public function testActionActivate()
    {

        //Yii::$app->runAction('extension/activate', ['vendor/package1']);
    }

    public function testActionDummy()
    {
        $this->assertEquals(0, Yii::$app->runAction('extension/dummy', ['message']));
    }

}