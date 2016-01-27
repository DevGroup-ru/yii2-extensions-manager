<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\helpers\ExtensionsHelper;
use Yii;
use yii\web\Application;

class ExtensionsHelperTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        self::writeExtFile();
    }

    public static function tearDownAfterClass()
    {
        self::writeExtFile();
    }

    protected static function writeExtFile()
    {
        $fn = __DIR__ . '/config/extensions.php';
        if (true === file_exists($fn)) {
            unlink($fn);
        }
        copy(__DIR__ . '/data/extensions.php', __DIR__ . '/config/extensions.php');
    }

    public function setUp()
    {
        $config = include 'config/testapp.php';
        new Application($config);
        Yii::$app->cache->flush();
        Yii::setAlias('@vendor', __DIR__ . '/testapp/vendor');
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

    public function testGetAllConfigurables()
    {
        $this->assertEquals(4, count(ExtensionsHelper::getConfigurables()));
    }

    public function testGetOnlyActive()
    {
        $this->assertEquals(1, count(ExtensionsHelper::getConfigurables(true)));
    }
}