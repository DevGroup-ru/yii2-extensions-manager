<?php
namespace DevGroup\ExtensionsManager\controllers;

use cebe\markdown\GithubMarkdown;
use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\ExtensionsManager\actions\ConfigurationIndex;
use DevGroup\ExtensionsManager\ExtensionsManager;
use Packagist\Api\Client;
use Packagist\Api\Result\Package\Version;
use yii\data\ArrayDataProvider;
use DevGroup\ExtensionsManager\models\Extension;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class ExtensionsController extends BaseController
{
    private static $packagist;
    private static $currentVersion;

    public function init()
    {
        parent::init();
    }

    public function actions()
    {
        return [
            'config' => [
                'class' => ConfigurationIndex::className(),
            ],
        ];
    }

    public function actionIndex()
    {

    }

    /**
     * @param string $sort
     * @param string $type
     * @param string $query
     * @return \DevGroup\AdminUtils\response\AjaxResponse|string
     */
    public function actionSearch($sort = '', $type = Extension::TYPE_DOTPLANT, $query = '')
    {
        $packagist = self::getPackagist();
        $filters = ['type' => $type];
        if (1 === preg_match('{([\\\\/])}', $query, $m)) {
            $queryArray = explode($m[0], $query);
            $filters['vendor'] = array_shift($queryArray);
        }
        $packages = $packagist->search($query, $filters);
        return $this->renderResponse(
            'search',
            [
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $packages,
                    'pagination' => [
                        'pageSize' => 10,
                    ],
                ]),
                'type' => $type,
            ]
        );
    }

    /**
     * @param $url
     * @param array $headers
     * @return mixed
     */
    private static function doRequest($url, $headers = [])
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

    /**
     * @return Client
     */
    private static function getPackagist()
    {
        if (true === empty(self::$packagist) || false === self::$packagist instanceof Client) {
            $packagist = new Client();
            $packagist->setPackagistUrl(static::module()->packagistUrl);
            self::$packagist = $packagist;
        }
        return self::$packagist;
    }

    /**
     * @param $repo
     * @return bool
     */
    public static function isGit($repo)
    {
        return false !== strpos($repo, 'github');
    }

    /**
     * @return \DevGroup\AdminUtils\response\AjaxResponse|string
     * @throws NotFoundHttpException
     */
    public function actionDetails()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException("Page not found");
        }
        $packageName = Yii::$app->request->post('packageName');
        $packagist = self::getPackagist();
        $package = $packagist->get($packageName);
        $repository = $package->getRepository();
        $packagistVersions = $package->getVersions();
        $readme = '';
        $versionsData = $dependencies = [];
        if (true === self::isGit($repository)) {
            $repository = preg_replace(['%^.*github.com\/%', '%\.git$%'], '', $repository);
            $gitAccessToken = self::module()->githubAccessToken;
            $gitApiUrl = rtrim(self::module()->githubApiUrl, '/');
            $applicationName = self::module()->applicationName;
            $headers = [
                'User-Agent: ' . $applicationName,
            ];
            if (true === empty($gitAccessToken)) {
                $headers[] = 'Authorization: token ' . $gitAccessToken;
            }
            $gitReadmeUrl = $gitApiUrl . '/repos/' . $repository . '/readme';
            $gitReleasesUrl = $gitApiUrl . '/repos/' . $repository . '/releases';
            $readmeData = self::doRequest($gitReadmeUrl, $headers);
            $readme = self::humanizeReadme($readmeData);
            $versionsData = Json::decode(self::doRequest($gitReleasesUrl, $headers));
            if (true === empty($versionsData)) {
                $gitTagsUrl = $gitApiUrl . '/repos/' . $repository . '/tags';
                $versionsData = Json::decode(self::doRequest($gitTagsUrl, $headers));
            }
        }
        $versions = self::getVersions($packagistVersions, array_shift($versionsData));
        $jsonUrl = rtrim(self::module()->packagistUrl, '/') . '/packages/' . trim($packageName, '/ ') . '.json';
        $packageJson = self::doRequest($jsonUrl);
        $packageData = Json::decode($packageJson);
        $description = self::getPackageDataField($packageData, 'description');
        $name = self::getPackageDataField($packageData, 'name');
        $dependencies['require'] = self::getOtherPackageData($packageData, 'require');
        $dependencies['require-dev'] = self::getOtherPackageData($packageData, 'require-dev');
        $authors = self::getOtherPackageData($packageData, 'authors');
        $license = self::getOtherPackageData($packageData, 'license');

        return $this->renderResponse(
            '_ext-details',
            [
                'readme' => $readme,
                'versions' => $versions,
                'description' => $description,
                'name' => $name,
                'dependencies' => $dependencies,
                'authors' => $authors,
                'license' => $license,
                'packageName' => $packageName,
            ]
        );
    }

    /**
     * @param $data
     * @param string $field
     * @return string
     */
    private static function getPackageDataField($data, $field)
    {
        $string = '';
        $langId = Yii::$app->language;
        if (false === empty($data['package']['versions'][self::$currentVersion]['extra']['yii2-extension'][$field . '_' . $langId])) {
            $string = $data['package']['versions'][self::$currentVersion]['extra']['yii2-extension'][$field . '_' . $langId];
        } else if (false === empty($data['package']['versions'][self::$currentVersion][$field])) {
            $string = $data['package']['versions'][self::$currentVersion]['description'];
        } else if (false === empty($data['package']['description'])) {
            $string = $data['package']['description'];
        }
        return $string;
    }

    /**
     * @param $data
     * @param $key
     * @return array
     */
    public static function getOtherPackageData($data, $key)
    {
        $out = [];
        if (false === empty($data['package']['versions'][self::$currentVersion][$key])) {
            $out = $data['package']['versions'][self::$currentVersion][$key];
        }
        return $out;
    }

    /**
     * @param $data
     * @return string
     */
    private static function humanizeReadme($data)
    {
        $readme = '';
        $data = Json::decode($data);
        if (false === empty($data['content'])) {
            $content = base64_decode(str_replace('\n', '', $data['content']));
            $parser = new GithubMarkdown();
            $readme = $parser->parse($content);
        }
        return $readme;
    }

    /**
     * @param array $packagistVersions
     * @param array | null $gitCurrent can be item array of git releases or git tags
     * for other usages we need tag name not release name. If $gitCurrent item of releases, it must have 'tag_name' key
     * otherwise 'name' key. Release item has 'name' key too, but this is not the key we are looking for.
     * @return array
     */
    private static function getVersions($packagistVersions, $gitCurrent)
    {
        $versions = [];
        $current = '';
        if (null !== $gitCurrent) {
            if (false === empty($gitCurrent['tag_name'])) {
                $current = $gitCurrent['tag_name'];
            } else if (false === empty ($gitCurrent['name'])) {
                $current = $gitCurrent['name'];
            }
        }
        foreach ($packagistVersions as $name => $data) {
            /** @var Version $data */
            if ($current == $name) {
                $versions['current'] = $name;
            }
            $versions[$name] = $data->getTime();
        }
        if (true === empty($versions['current']) && false === empty($versions)) {
            reset($versions);
            $current = key($versions);
            $versions['current'] = $current;
        }
        self::$currentVersion = $current;
        return $versions;
    }

    public function actionInstall()
    {

    }

    /**
     * @return null| ExtensionsManager
     */
    public static function module()
    {
        return Yii::$app->getModule('extensions-manager');
    }
}