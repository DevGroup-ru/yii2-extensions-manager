<?php

namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\models\Extension;
use testsHelper\CustomClient;
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
        $config = include __DIR__ . '/../../testapp/config/web.php';
        new Application($config);
        Yii::$app->cache->flush();
        Yii::setAlias('@vendor', __DIR__ . '/../../testapp/vendor');
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
        TestConfigCleaner::cleanExtensions();
        TestConfigCleaner::cleanTestConfigs();
    }

    public function testNotEmptyVersionsTagsAndReleases()
    {
        self::prepareAll(
            __DIR__ . '/../../data/mpdf.json',
            __DIR__ . '/../../data/mpdf-releases.json',
            __DIR__ . '/../../data/mpdf-tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testNotEmptyVersionsTags()
    {
        self::prepareAll(
            __DIR__ . '/../../data/mpdf.json',
            __DIR__ . '/../../data/releases.json',
            __DIR__ . '/../../data/mpdf-tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testNotEmptyVersionsReleases()
    {
        self::prepareAll(
            __DIR__ . '/../../data/mpdf.json',
            __DIR__ . '/../../data/mpdf-releases.json',
            __DIR__ . '/../../data/tags.json'
        );
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    public function testGetVersionsNoReleasesNoTags()
    {
        self::prepareAll(
            __DIR__ . '/../../data/extra-package.json',
            __DIR__ . '/../../data/releases.json',
            __DIR__ . '/../../data/tags.json'
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
        $readme = ExtensionDataHelper::humanizeReadme(file_get_contents(__DIR__ . '/../../data/readme.json'));
        $this->assertNotEmpty($readme);
    }

    public function testGetType()
    {
        self::$packageData = Json::decode(file_get_contents(__DIR__ . '/../../data/package.json'));
        $type = ExtensionDataHelper::getType(self::$packageData);
        $this->assertTrue(isset(Extension::getTypes()[$type]));
        return $type;
    }

    public function testInstalledGetType()
    {
        $data = ComposerInstalledSet::get()->getInstalled('fakedev2/yii2-fake-ext4');
        $type = ExtensionDataHelper::getType($data);
        $this->assertSame(4, count(ComposerInstalledSet::get()->getInstalled()));
        $this->assertSame('dotplant-extension', $type);
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
            Extension::TYPE_YII,
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
            Extension::TYPE_YII,
            'name'
        );
        $this->assertEquals('Fourth fake extension', $name);
    }

    public function testGetLocalizedDataFieldNoExtra()
    {
        $name = ExtensionDataHelper::getLocalizedDataField(
            Json::decode(file_get_contents(__DIR__ . '/../../data/installed-no-extra.json')),
            Extension::TYPE_YII,
            'name'
        );
        $this->assertEquals('devgroup/yii2-extensions-manager', $name);
    }

    public function testGetLocalizedVersionedDataFieldNoExtra()
    {
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(self::$packageData, Extension::TYPE_YII, 'description');
        $this->assertNotEmpty($description);
    }

    public function testGetLocalizedVersionedDataFieldExtra()
    {
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/../../data/extra-package.json')),
            Extension::TYPE_YII,
            'description');
        $expected = 'type.version.extra.description_ru';
        $this->assertEquals($expected, $description);
    }

    public function testGetLocalizedVersionedDataFieldExtraWrongLang()
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/../../data/extra-package.json')),
            Extension::TYPE_YII,
            'description');
        $expected = 'type.version.extra.description';
        $this->assertEquals($expected, $description);
    }

    public function testGetLocalizedVersionedDataFieldExtraVersionField()
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/../../data/no-extra-package.json')),
            Extension::TYPE_YII,
            'description');
        $expected = 'type.version.description';
        $this->assertEquals($expected, $description);
    }

    public function testGetLocalizedVersionedDataFieldExtraPackageField()
    {
        Yii::$app->language = 'zn';
        $description = ExtensionDataHelper::getLocalizedVersionedDataField(
            Json::decode(file_get_contents(__DIR__ . '/../../data/no-version-package.json')),
            Extension::TYPE_YII,
            'description');
        $expected = 'description';
        $this->assertEquals($expected, $description);
    }

    public function testGetOtherPackageVersionedDataString()
    {
        $description = ExtensionDataHelper::getOtherPackageVersionedData(
            Json::decode(file_get_contents(__DIR__ . '/../../data/extra-package.json')),
            'description',
            false
        );
        $expected = 'type.version.description';
        $this->assertEquals($expected, $description);
    }

    public function testGetOtherPackageVersionedDataAsArray()
    {
        $description = ExtensionDataHelper::getOtherPackageVersionedData(
            Json::decode(file_get_contents(__DIR__ . '/../../data/extra-package.json')),
            'description'
        );
        $this->assertTrue(in_array('type.version.description', $description));
    }

    public function testGetOtherPackageDataString()
    {
        $name = ExtensionDataHelper::getOtherPackageData(
            Json::decode(file_get_contents(__DIR__ . '/../../data/installed-no-extra.json')),
            'name'
        );
        $this->assertEquals('devgroup/yii2-extensions-manager', $name);
    }

    public function testGetOtherPackageDataStringAsArray()
    {
        $name = ExtensionDataHelper::getOtherPackageData(
            Json::decode(file_get_contents(__DIR__ . '/../../data/installed-no-extra.json')),
            'name',
            true
        );
        $this->assertTrue(in_array('devgroup/yii2-extensions-manager', $name));
    }

    protected function prepareAll($package, $releases, $tags)
    {
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