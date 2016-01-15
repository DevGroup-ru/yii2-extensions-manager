<?php
namespace DevGroup\ExtensionsManager\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\DeferredTasks\actions\ReportQueueItem;
use DevGroup\DeferredTasks\helpers\DeferredHelper;
use DevGroup\DeferredTasks\helpers\ReportingTask;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\ExtensionsManager\actions\ConfigurationIndex;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use Packagist\Api\Client;
use Symfony\Component\Process\ProcessBuilder;
use yii\data\ArrayDataProvider;
use DevGroup\ExtensionsManager\models\Extension;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ExtensionsController extends BaseController
{
    /** @var  Client packagist.org API client instance */
    private static $packagist;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'config' => [
                'class' => ConfigurationIndex::className(),
            ],
            'deferred-report-queue-item' => [
                'class' => ReportQueueItem::className(),
            ],
        ];
    }

    /**
     *
     */
    public function actionIndex()
    {
        $extensions = self::module()->getExtensions();
        return $this->render(
            'index',
            [
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $extensions,
                    'pagination' => [
                        'defaultPageSize' => 10,
                        'pageSize' => self::module()->extensionsPerPage,
                    ],
                ]),
            ]
        );
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
                        'defaultPageSize' => 10,
                        'pageSize' => self::module()->extensionsPerPage,
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
            if (false === empty($gitAccessToken)) {
                $headers[] = 'Authorization: token ' . $gitAccessToken;
            }
            $gitReadmeUrl = $gitApiUrl . '/repos/' . $repository . '/readme';
            $gitReleasesUrl = $gitApiUrl . '/repos/' . $repository . '/releases';
            $readmeData = self::doRequest($gitReadmeUrl, $headers);
            $readme = ExtensionDataHelper::humanizeReadme($readmeData);
            $versionsData = Json::decode(self::doRequest($gitReleasesUrl, $headers));
            if (true === empty($versionsData)) {
                $gitTagsUrl = $gitApiUrl . '/repos/' . $repository . '/tags';
                $versionsData = Json::decode(self::doRequest($gitTagsUrl, $headers));
            }
        }
        //ExtensionDataHelper::getVersions() must be invoked before other methods who fetches versioned data
        $versions = ExtensionDataHelper::getVersions($packagistVersions, array_shift($versionsData));
        $jsonUrl = rtrim(self::module()->packagistUrl, '/') . '/packages/' . trim($packageName, '/ ') . '.json';
        $packageJson = self::doRequest($jsonUrl);
        $packageData = Json::decode($packageJson);
        $type = ExtensionDataHelper::getType($packageData);

        return $this->renderResponse(
            '_ext-details',
            [
                'readme' => $readme,
                'versions' => $versions,
                'description' => ExtensionDataHelper::getLocalizedVersionedDataField($packageData, $type, 'description'),
                'name' => ExtensionDataHelper::getLocalizedVersionedDataField($packageData, $type, 'name'),
                'dependencies' => [
                    'require' => ExtensionDataHelper::getOtherPackageVersionedData($packageData, 'require'),
                    'require-dev' => ExtensionDataHelper::getOtherPackageVersionedData($packageData, 'require-dev'),
                ],
                'authors' => ExtensionDataHelper::getOtherPackageVersionedData($packageData, 'authors'),
                'license' => ExtensionDataHelper::getOtherPackageVersionedData($packageData, 'license'),
                'packageName' => $packageName,
                'installed' => array_key_exists($packageName, self::module()->getExtensions()),
                'type' => $type,
            ]
        );
    }


    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionRunTask()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException('Page not found');
        }
        $taskType = Yii::$app->request->post('taskType');
        $packageName = Yii::$app->request->post('packageName');
        switch ($taskType) {
            case ExtensionsManager::INSTALL_DEFERRED_TASK :
                return self::runTask(
                    [
                        './composer.phar',
                        'require',
                        $packageName
                    ],
                    ExtensionsManager::COMPOSER_INSTALL_DEFERRED_GROUP
                );
            case ExtensionsManager::UNINSTALL_DEFERRED_TASK :
                return self::runTask(
                    [
                        './composer.phar',
                        'remove',
                        $packageName,
                        '--update-with-dependencies',
                    ],
                    ExtensionsManager::COMPOSER_INSTALL_DEFERRED_GROUP
                );
            case ExtensionsManager::ACTIVATE_DEFERRED_TASK :
                return self::changeState($packageName);
            case ExtensionsManager::DEACTIVATE_DEFERRED_TASK :
                return self::changeState($packageName, true);
            default:
                // unrecognized task
        }
    }

    /**
     * @param $packageName
     * @param bool $deactivate
     * @return array
     * @throws ServerErrorHttpException
     */
    private static function changeState($packageName, $deactivate = false)
    {
        $extData = ComposerInstalledSet::get()->getInstalled($packageName);
        $type = ExtensionDataHelper::getType($extData);
        $packagePath = '@vendor' . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR;
        $taskCommand = [];
        $statusCode = '"0"';
        if ($type == Extension::TYPE_DOTPLANT) {
            $packageMigrations = ExtensionDataHelper::getInstalledExtraData($extData, 'migrationPath', true);
            foreach ($packageMigrations as $migrationPath) {
                Yii::$app->params['yii.migrations'][] = $packagePath . $migrationPath;
            }
            $taskCommand = [
                realpath(Yii::getAlias('@app') . '/yii'),
                'migrate/' . (true === $deactivate ? 'down' : 'up'),
                '--color=0',
                '--interactive=0',
                true === $deactivate ? 65536 : 0,
                ';',
            ];
            $statusCode = '"$?"';
        }
        $taskCommand = array_merge($taskCommand,
            [
                realpath(Yii::getAlias('@app') . '/yii'),
                'extension/mark-active',
                $packageName,
                $statusCode,

            ]
        );
        $group = true === $deactivate
            ? ExtensionsManager::EXTENSION_DEACTIVATE_DEFERRED_GROUP
            : ExtensionsManager::EXTENSION_ACTIVATE_DEFERRED_GROUP;
        return self::runTask(
            $taskCommand,
            $group
        );
    }

    /**
     * @param $command
     * @param $groupName
     * @return array
     * @throws ServerErrorHttpException
     */
    private static function runTask($command, $groupName)
    {
        if (null === $group = DeferredGroup::findOne(['name' => $groupName])) {
            $group = new DeferredGroup();
            $group->loadDefaultValues();
            $group->name = $groupName;
            $group->group_notifications = 0;
            $group->save();
        }
        if (intval($group->group_notifications) != 0) {
            // otherwise DeferredController 'deferred-queue-complete' event will not trigger
            // and we'll unable to write config
            $group->group_notifications = 0;
            $group->save(['group_notifications']);
        }
        $task = new ReportingTask();
        $task->model()->deferred_group_id = $group->id;
        $task->cliCommand(PHP_BINDIR . '/php', $command);
        if ($task->registerTask()) {
            DeferredHelper::runImmediateTask($task->model()->id);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'queueItemId' => $task->model()->id,
            ];
        } else {
            throw new ServerErrorHttpException("Unable to start task");
        }
    }

    /**
     * @return null| ExtensionsManager
     */
    public static function module()
    {
        return Yii::$app->getModule('extensions-manager');
    }
}