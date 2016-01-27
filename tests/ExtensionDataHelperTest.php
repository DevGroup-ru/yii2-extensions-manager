<?php
namespace DevGroup\ExtensionsManager\tests;

use DevGroup\ExtensionsManager\controllers\ExtensionsController;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use Packagist\Api\Client;
use Yii;
use yii\helpers\Json;
use yii\web\Application;

class ExtensionDataHelperTest extends \PHPUnit_Framework_TestCase
{
    protected static $module;
    protected static $versionsData;
    protected static $packagistVersions;
    protected static $readmeData;

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

    public function testGetVersions()
    {
        self::prepareAll();
        $versions = ExtensionDataHelper::getVersions(self::$packagistVersions, array_shift(self::$versionsData));
        $this->assertNotEmpty($versions);
        $this->assertTrue(isset($versions['current']));
    }

    /**
     * @depends testGetVersions
     */
    public function testHumanizeReadme()
    {
        $readme = ExtensionDataHelper::humanizeReadme(self::$readmeData);
        $this->assertNotEmpty($readme);
    }

    protected function prepareAll()
    {
        /** @var ExtensionsManager $module */
        self::$module = Yii::$app->getModule('extensions-manager');
        $packagist = new Client();
        $packagist->setPackagistUrl(self::$module->packagistUrl);
        $package = $packagist->get('yiisoft/yii2');
        $repository = $package->getRepository();
        self::$packagistVersions = $package->getVersions();
        $versionsData = $dependencies = [];
        if (true === ExtensionsController::isGit($repository)) {
            $repository = preg_replace(['%^.*github.com\/%', '%\.git$%'], '', $repository);
            $gitAccessToken = self::$module->githubAccessToken;
            $gitApiUrl = rtrim(self::$module->githubApiUrl, '/');
            $applicationName = self::$module->applicationName;
            $headers = [
                'User-Agent: ' . $applicationName,
            ];
            if (false === empty($gitAccessToken)) {
                $headers[] = 'Authorization: token ' . $gitAccessToken;
            }
            $gitReadmeUrl = $gitApiUrl . '/repos/' . $repository . '/readme';
            $gitReleasesUrl = $gitApiUrl . '/repos/' . $repository . '/releases';
            $readmeData = self::doRequest($gitReadmeUrl, $headers);
            $versionsData = Json::decode(self::doRequest($gitReleasesUrl, $headers));
            if (true === empty($versionsData)) {
                $gitTagsUrl = $gitApiUrl . '/repos/' . $repository . '/tags';
                $versionsData = Json::decode(self::doRequest($gitTagsUrl, $headers));
            }
            self::$readmeData = $readmeData;
        }
        self::$versionsData = $versionsData;
    }

    protected static function doRequest($url, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        if (0 !== $errno = curl_errno($ch)) {
            $errorMessage = curl_strerror($errno);
            Yii::$app->session->setFlash("cURL error ({$errno}):\n {$errorMessage}");
        }
        curl_close($ch);
        return $response;
    }
}