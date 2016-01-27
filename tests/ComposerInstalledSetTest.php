<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use yii\web\Application;
use Yii;

class ComposerInstalledSetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = include 'config/testapp.php';
        $app = new Application($config);
        Yii::$app->cache->flush();
        Yii::setAlias('@vendor', __DIR__ . '/testapp/vendor');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        if (\Yii::$app && \Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }
        \Yii::$app = null;
    }

    public function testCannotInstantiateExternally()
    {
        $reflection = new \ReflectionClass(ComposerInstalledSet::class);
        $constructor = $reflection->getConstructor();
        $this->assertFalse($constructor->isPublic());
    }

    public function testGet()
    {
        $fn = Yii::getAlias('@vendor') . '/composer/installed.json';
        if (false === file_exists($fn)) {
            $this->markTestSkipped('In this case we cant perform tests below.' . PHP_EOL);
        }
        $instance = ComposerInstalledSet::get();
        $this->assertInstanceOf(ComposerInstalledSet::class, $instance);
        return $instance;
    }

    /**
     * @depends testGet
     * @param ComposerInstalledSet $instance
     * @return array
     */
    public function testGetInstalledExisting(ComposerInstalledSet $instance)
    {
        $array = $instance::get()->getInstalled();
        $this->assertNotEmpty($array);
        return $array;
    }

    /**
     * @depends testGetInstalledExisting
     * @param array $installedArray
     */
    public function testGetByName(array $installedArray)
    {
        $name = array_pop(array_keys($installedArray));
        $this->assertNotEmpty(ComposerInstalledSet::get()->getInstalled($name));
    }

}