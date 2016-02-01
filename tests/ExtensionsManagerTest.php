<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\ExtensionsManager;
use testsHelper\TestConfigCleaner;
use Yii;
use yii\console\Application;

class ExtensionsManagerTest extends \PHPUnit_Framework_TestCase
{
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

    public static function tearDownAfterClass()
    {
        TestConfigCleaner::cleanExtensions();
    }

    public function testGetModule()
    {
        $module = Yii::$app->getModule('extensions-manager');
        $this->assertInstanceOf('DevGroup\ExtensionsManager\ExtensionsManager', $module);
        return $module;
    }

    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testGetExtensionsFromCache(ExtensionsManager $module)
    {
        $a = $module->getExtensions();
        $this->assertEquals(4, count($a));
    }

    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testGetExtensionsIgnoreCache(ExtensionsManager $module)
    {
        $a = $module->getExtensions('', true);
        $this->assertEquals(4, count($a));
    }

    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     * @return string
     */
    public function testGetExtensionByName(ExtensionsManager $module)
    {
        $a = $module->getExtensions('fakedev2/yii2-fake-ext4');
        $this->assertNotEmpty($a);
        $this->assertEquals('fakedev2/yii2-fake-ext4', $a['composer_name']);
        return $a['composer_name'];
    }

    /**
     * @depends testGetModule
     * @depends testGetExtensionByName
     * @param ExtensionsManager $module
     * @param string $name
     */
    public function testExtensionIsActive(ExtensionsManager $module, $name)
    {
        $this->assertTrue($module->extensionIsActive($name));
        $this->assertFalse($module->extensionIsCore($name));
    }

    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testGetExtensionIsCore(ExtensionsManager $module)
    {
        $a = $module->getExtensions('fakedev/yii2-fake-ext3');
        $this->assertTrue($module->extensionIsCore($a['composer_name']));
        $this->assertFalse($module->extensionIsActive($a['composer_name']));
    }

    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testNotExistingExtension(ExtensionsManager $module)
    {
        $a = $module->getExtensions('notexisting/extension');
        $this->assertEmpty($a);
    }
    /**
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testBrokenExtension(ExtensionsManager $module)
    {
        TestConfigCleaner::removeExtFile();
        copy(__DIR__ . '/data/broken-extensions.php', __DIR__ . '/config/extensions.php');
        $a = $module->getExtensions('broken/extension1', true);
        $this->assertNotEmpty($a);
        $this->assertFalse($module->extensionIsCore('broken/extension1'));
        $this->assertFalse($module->extensionIsActive('broken/extension1'));
    }

    /**
     * This test runs last, because it overrides extensions.php file from installed.json
     * and we have no activated and core extensions there
     *
     * @depends testGetModule
     * @param ExtensionsManager $module
     */
    public function testGetExtensionsNoFile(ExtensionsManager $module){
        TestConfigCleaner::removeExtFile();
        $a = $module->getExtensions('', true);
        $this->assertTrue(TestConfigCleaner::checkExtFile());
        $this->assertEquals(4, count($a));
    }
}