<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\helpers\ExtensionFileWriter;
use Yii;
use yii\console\Application;

class ExtensionFileWriterTest extends \PHPUnit_Framework_TestCase
{

    public static function tearDownAfterClass()
    {
        self::removeExtFile();
        copy(__DIR__ . '/data/extensions.php', __DIR__ . '/config/extensions.php');
    }

    protected static function removeExtFile()
    {
        $fn = __DIR__ . '/config/extensions.php';
        if (true === file_exists($fn)) {
            unlink($fn);
        }
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

    public function checkFile()
    {
        $a = [];
        $fn = __DIR__ . '/config/extensions.php';
        if (false === file_exists($fn)) {
            $this->markTestSkipped();
        } else {
            $a = include $fn;
        }
        $this->assertNotEmpty($a);
        return $a;
    }

    public function testUpdateConfig()
    {
        $a = $this->checkFile();
        ExtensionFileWriter::updateConfig();
        $this->assertEquals(4, count($a));
    }

    public function testUpdateWithAddition()
    {
        self::removeExtFile();
        copy(__DIR__ . '/data/less-extensions.php', __DIR__ . '/config/extensions.php');
        ExtensionFileWriter::updateConfig();
        $a = $this->checkFile();
        $this->assertEquals(4, count($a));
    }

    public function testUpdateWithDeletion()
    {
        self::removeExtFile();
        copy(__DIR__ . '/data/more-extensions.php', __DIR__ . '/config/extensions.php');
        ExtensionFileWriter::updateConfig();
        $a = $this->checkFile();
        $this->assertEquals(4, count($a));
    }
}