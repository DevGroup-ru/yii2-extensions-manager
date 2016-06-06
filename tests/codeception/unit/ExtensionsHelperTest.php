<?php

namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\helpers\ExtensionsHelper;
use testsHelper\TestConfigCleaner;
use Yii;
use yii\web\Application;

class ExtensionsHelperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        TestConfigCleaner::cleanExtensions();
        $config = include __DIR__ . '/../../testapp/config/console.php';
        new Application($config);
        Yii::$app->cache->flush();
        Yii::setAlias('@vendor', __DIR__ . '/../../testapp/vendor');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        if (Yii::$app && Yii::$app->has('session', true)) {
            Yii::$app->session->close();
        }
        Yii::$app = null;
        TestConfigCleaner::cleanExtensions();
        TestConfigCleaner::cleanTestConfigs();
    }

    public function testGetAllConfigurables()
    {
        $this->assertEquals(4, count(ExtensionsHelper::getConfigurables()));
    }

    public function testGetOnlyActive()
    {
        $this->assertEquals(1, count(ExtensionsHelper::getConfigurables(true)));
    }
}
