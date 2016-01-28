<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\models\Extension;
use testsHelper\TestConfigCleaner;
use Yii;
use yii\helpers\Json;
use yii\web\Application;

class ExtensionDataHelperTest extends \PHPUnit_Framework_TestCase
{
    protected static $module;
    protected static $versionsData;
    protected static $packagistVersions;
    protected static $packageData;

    public function setUp()
    {
        $config = include 'config/testapp.php';
        new Application($config);
        Yii::$app->cache->flush();
        Yii::setAlias('@vendor', __DIR__ . '/testapp/vendor');
        Yii::$app->language = 'ru';
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

    public function testNotEmptyVersionsTagsAndReleases()
    {
        self::prepareAll(
            __DIR__ . '/data/mpdf.json',
            __DIR__ . '/data/mpdf-releases.json',
            __DIR__ . '/data/mpdf-tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testNotEmptyVersionsTags()
    {
        self::prepareAll(
            __DIR__ . '/data/mpdf.json',
            __DIR__ . '/data/releases.json',
            __DIR__ . '/data/mpdf-tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testNotEmptyVersionsReleases()
    {
        self::prepareAll(
            __DIR__ . '/data/mpdf.json',
            __DIR__ . '/data/mpdf-releases.json',
            __DIR__ . '/data/tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testGetVersionsNoReleasesNoTags()
    {
        self::prepareAll(
            __DIR__ . '/data/extra-package.json',
            __DIR__ . '/data/releases.json',
            __DIR__ . '/data/tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    /**
     * @depends testGetVersionsNoReleasesNoTags
     */
    public function testHumanizeReadme()
    {
        $readme = ExtensionDataHelper::humanizeReadme(file_get_contents(__DIR__ . '/data/readme.json'));
        $this->assertNotEmpty($readme);
    }

    public function testGetType()
    {
        self::$packageData = Json::decode(file_get_contents(__DIR__ . '/data/package.json'));
        $type = ExtensionDataHelper::getType(self::$packageData);
        $this->assertTrue(isset(Extension::getTypes()[$type]));
        return $type;
    }

    public function testInstalledGetType()
    {
        $data = ComposerInstalledSet::get()->getInstalled('fakedev2/yii2-fake-ext4');
        $type = ExtensionDataHelper::getType($data);
        $this->assertTrue(isset(Extension::getTypes()[$type]));
        return $data;
    }

    /**
     * @depends testInstalledGetType
     * @param array $data
     */
    public function testGetInstalledExtraData(array $data)
    {
        $descr = ExtensionDataHelper::getInstalledExtraData($data, 'translationCategory');
        $this->assertEquals('fake-four', $descr);
    }

    /**
     * @depends testInstalledGetType
     * @param array $data
     */
    public function testGetInstalledExtraDataArray(array $data)
    {
        $mp = ExtensionDataHelper::getInstalledExtraData($data, 'migrationPath', true);
        $this->assertNotEmpty($mp);
    }

    /**
     * @depends testInstalledGetType
     * @param array $data
     */
    public function testGetLocalizedDataField(array $data)
    {
        $name = ExtensionDataHelper::getLocalizedDataField(
            $data,
            'yii2-extension',
            'name'
        );
        $this->assertEquals('Четвертое тестовое расширение', $name);
    }

    /**
     * @depends testInstalledGetType
     * @param array $data
     */
    public function testGetLocalizedDataFieldWrongLang(array $data)
    {
        Yii::$app->language = 'zn';
        $name = ExtensionDataHelper::getLocalizedDataField(
            $data,
            'yii2-extension',
            'name'
        );
        $this->assertEquals('Fourth fake extension', $name);
    }

    public function testGetLocalizedDataFieldNoExtra()
    {
        $name = ExtensionDataHelper::getLocalizedDataField(
            Json::decode(file_get_contents(__DIR__ . '/data/installed-no-extra.json')),
            'yii2-extension',
            'name'
        );
        $this->assertEquals('devgroup/yii2-extensions-manager', $name);
    }

    /**
     * @depends testGetType
     * @param $type
     */
    public function testGetLocalizedVersionedDataFieldNoExtra($type)
    {
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(self::$packageData, $type, 'description');
        $this->assertNotEmpty($description);
    }

    /**
     * @depends testGetType
     * @param $type
     */
    public function testGetLocalizedVersionedDataFieldExtra($type)
    {
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/data/extra-package.json')),
            $type,
            'description');
        $expected = 'type.version.extra.description_ru';
        $this->assertEquals($expected, $description);
    }

    /**
     * @depends testGetType
     * @param $type
     */
    public function testGetLocalizedVersionedDataFieldExtraWrongLang($type)
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/data/extra-package.json')),
            $type,
            'description');
        $expected = 'type.version.extra.description';
        $this->assertEquals($expected, $description);
    }

    /**
     * @depends testGetType
     * @param $type
     */
    public function testGetLocalizedVersionedDataFieldExtraVersionField($type)
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/data/no-extra-package.json')),
            $type,
            'description');
        $expected = 'type.version.description';
        $this->assertEquals($expected, $description);
    }

    /**
     * @depends testGetType
     * @param $type
     */
    public function testGetLocalizedVersionedDataFieldExtraPackageField($type)
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/data/no-version-package.json')),
            $type,
            'description');
        $expected = 'description';
        $this->assertEquals($expected, $description);
    }

    public function testGetOtherPackageVersionedDataString()
    {
        $description = ExtensionDataHelper::getOtherPackageVersionedData(
            Json::decode(file_get_contents(__DIR__ . '/data/extra-package.json')),
            'description',
            false
        );
        $expected = 'type.version.description';
        $this->assertEquals($expected, $description);
    }

    public function testGetOtherPackageVersionedDataAsArray()
    {
        $description = ExtensionDataHelper::getOtherPackageVersionedData(
            Json::decode(file_get_contents(__DIR__ . '/data/extra-package.json')),
            'description'
        );
        $this->assertTrue(in_array('type.version.description', $description));
    }

    public function testGetOtherPackageDataString()
    {
        $name = ExtensionDataHelper::getOtherPackageData(
            Json::decode(file_get_contents(__DIR__ . '/data/installed-no-extra.json')),
            'name'
        );
        $this->assertEquals('devgroup/yii2-extensions-manager', $name);
    }

    public function testGetOtherPackageDataStringAsArray()
    {
        $name = ExtensionDataHelper::getOtherPackageData(
            Json::decode(file_get_contents(__DIR__ . '/data/installed-no-extra.json')),
            'name',
            true
        );
        $this->assertTrue(in_array('devgroup/yii2-extensions-manager', $name));
    }

    protected function prepareAll($package, $releases, $tags)
    {
        Yii::$classMap['DevGroup\ExtensionsManager\tests\CustomClient'] = __DIR__ . '/CustomClient.php';
        /** @var ExtensionsManager $module */
        self::$module = Yii::$app->getModule('extensions-manager');
        $packagist = new CustomClient();
        $package = $packagist->get($package);
        self::$packagistVersions = $package->getVersions();
        $versionsData = Json::decode(file_get_contents($releases));
        if (true === empty($versionsData)) {
            $versionsData = Json::decode(file_get_contents($tags));
        }
        self::$versionsData = $versionsData;
    }

    public static function tearDownAfterClass()
    {
        TestConfigCleaner::cleanTestConfigs();
    }
}