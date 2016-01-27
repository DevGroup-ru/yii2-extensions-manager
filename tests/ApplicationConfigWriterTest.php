<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\helpers\ApplicationConfigWriter;
use Yii;
use yii\web\Application;

class ApplicationConfigWriterTest  extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testInitNullFile()
    {
        new ApplicationConfigWriter([
            'filename' => null,
        ]);
    }
}