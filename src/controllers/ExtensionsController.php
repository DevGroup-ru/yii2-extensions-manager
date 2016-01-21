<?php
namespace DevGroup\ExtensionsManager\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\DeferredTasks\actions\ReportQueueItem;
use DevGroup\DeferredTasks\helpers\DeferredHelper;
use DevGroup\DeferredTasks\helpers\ReportingChain;
use DevGroup\DeferredTasks\helpers\ReportingTask;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\ExtensionsManager\actions\ConfigurationIndex;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use Packagist\Api\Client;
use Symfony\Component\Process\ProcessBuilder;
use yii\base\InvalidParamException;
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
     *Shows installed extensions
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
     * Searching extensions packages using packagist API.
     * Pckagist API gives us ability to filter packages by type and vendor.
     * Supported types are: Extension::getTypes();
     * Vendor filter extracts from query string. If query string contains / or \ all string before it will be
     * recognized as vendor and added into API query.
     *
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
     * Process API requests to github and packagist
     *
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
     * Method collects an extended package data from packagist.org and github.com
     * other services are not supported yet.
     *
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
     * Common method to access from web via ajax requests. Builds a ReportingChain and immediately fires it.
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws InvalidParamException
     */
    public function actionRunTask()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException('Page not found');
        }
        $packageName = Yii::$app->request->post('packageName');
        $extension = self::module()->getExtensions($packageName);
        if (true === empty($extension)) {
            return self::runTask(
                [
                    realpath(Yii::getAlias('@app') . '/yii'),
                    'extension/dummy',
                    Yii::t('extensions-manager', 'Undefined extension!')
                ],
                ExtensionsManager::EXTENSION_DUMMY_DEFERRED_GROUP
            );
        }
        $taskType = Yii::$app->request->post('taskType');
        $chain = new ReportingChain();
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
                self::uninstall($extension, $chain);
                break;
            case ExtensionsManager::ACTIVATE_DEFERRED_TASK :
                self::activate($extension, $chain);
                break;
            case ExtensionsManager::DEACTIVATE_DEFERRED_TASK :
                self::deactivate($extension, $chain);
                break;
            default:
                return self::runTask(
                    [
                        realpath(Yii::getAlias('@app') . '/yii'),
                        'extension/dummy',
                        Yii::t('extensions-manager', 'Unrecognized task!')
                    ],
                    ExtensionsManager::EXTENSION_DUMMY_DEFERRED_GROUP
                );
        }
        if (null !== $firstTaskId = $chain->registerChain()) {
            DeferredHelper::runImmediateTask($firstTaskId);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'queueItemId' => $firstTaskId,
            ];
        } else {
            throw new ServerErrorHttpException("Unable to start chain");
        }
    }

    /**
     * Adds deactivation task into ReportingChain if Extension is active
     *
     * @param array $extension
     * @param ReportingChain $chain
     */
    private static function deactivate($extension, ReportingChain $chain)
    {
        if ($extension['is_active'] == 1) {
            self::prepareMigrationTask(
                $extension,
                $chain,
                ExtensionsManager::MIGRATE_TYPE_DOWN,
                ExtensionsManager::EXTENSION_DEACTIVATE_DEFERRED_GROUP
            );
            $deactivationTask = self::buildTask(
                [
                    realpath(Yii::getAlias('@app') . '/yii'),
                    'extension/deactivate',
                    $extension['composer_name'],
                ],
                ExtensionsManager::EXTENSION_DEACTIVATE_DEFERRED_GROUP
            );
            $chain->addTask($deactivationTask);
        } else {
            $dummyTask = self::buildTask(
                [
                    realpath(Yii::getAlias('@app') . '/yii'),
                    'extension/dummy',
                    Yii::t('extensions-manager', 'Extension already deactivated!')
                ],
                ExtensionsManager::EXTENSION_DUMMY_DEFERRED_GROUP
            );
            $chain->addTask($dummyTask);
        }
    }

    /**
     * Adds activation task into ReportingChain if Extension is not active
     *
     * @param array $extension
     * @param ReportingChain $chain
     */
    private static function activate($extension, ReportingChain $chain)
    {
        if ($extension['is_active'] == 0) {
            self::prepareMigrationTask(
                $extension,
                $chain,
                ExtensionsManager::MIGRATE_TYPE_UP,
                ExtensionsManager::EXTENSION_ACTIVATE_DEFERRED_GROUP
            );
            $deactivationTask = self::buildTask(
                [
                    realpath(Yii::getAlias('@app') . '/yii'),
                    'extension/activate',
                    $extension['composer_name'],
                ],
                ExtensionsManager::EXTENSION_ACTIVATE_DEFERRED_GROUP
            );
            $chain->addTask($deactivationTask);
        } else {
            $dummyTask = self::buildTask(
                [
                    realpath(Yii::getAlias('@app') . '/yii'),
                    'extension/dummy',
                    Yii::t('extensions-manager', 'Extension already activated!')
                ],
                ExtensionsManager::EXTENSION_DUMMY_DEFERRED_GROUP
            );
            $chain->addTask($dummyTask);
        }
    }

    /**
     * Adds uninstall task into ReportingChain
     *
     * @param $extension
     * @param ReportingChain $chain
     */
    private static function uninstall($extension, ReportingChain $chain)
    {
        self::deactivate($extension, $chain);

        $uninstallTask = self::buildTask(
            [
                './composer.phar',
                'remove',
                $extension['composer_name'],
                '--update-with-dependencies',
            ],
            ExtensionsManager::COMPOSER_UNINSTALL_DEFERRED_GROUP
        );
        $chain->addTask($uninstallTask);
    }

    /**
     * Prepares migration command
     *
     * @param array $ext
     * @param ReportingChain $chain
     * @param string $way
     * @param $group
     */
    private static function prepareMigrationTask(
        array $ext,
        ReportingChain $chain,
        $way = ExtensionsManager::MIGRATE_TYPE_UP,
        $group
    )
    {
        if ($ext['composer_type'] == Extension::TYPE_DOTPLANT) {
            $extData = ComposerInstalledSet::get()->getInstalled($ext['composer_name']);
            $packageMigrations = ExtensionDataHelper::getInstalledExtraData($extData, 'migrationPath', true);
            $packagePath = '@vendor' . DIRECTORY_SEPARATOR . $ext['composer_name'] . DIRECTORY_SEPARATOR;
            foreach ($packageMigrations as $migrationPath) {
                $migrateTask = self::buildTask(
                    [
                        realpath(Yii::getAlias('@app') . '/yii'),
                        'migrate/' . $way,
                        '--migrationPath=' . $packagePath . $migrationPath,
                        '--color=0',
                        '--interactive=0',
                        '--disableLookup=true',
                        (ExtensionsManager::MIGRATE_TYPE_DOWN == $way ? 68888 : ''),
                    ],
                    $group
                );
                $chain->addTask($migrateTask);
            }
        }
    }

    /**
     * Builds ReportingTask and places it into certain group. Also if group is not exists yet, it will be created
     * with necessary parameters, such as group_notifications=0.
     *
     * @param array $command
     * @param string $groupName
     * @return ReportingTask
     */
    private static function buildTask($command, $groupName)
    {
        if (null === $group = DeferredGroup::findOne(['name' => $groupName])) {
            $group = new DeferredGroup();
            $group->loadDefaultValues();
            $group->name = $groupName;
            $group->email_notification = 0;
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
        return $task;
    }

    /**
     * Runs separated ReportingTask
     *
     * @param $command
     * @param $groupName
     * @return array
     * @throws ServerErrorHttpException
     */
    private static function runTask($command, $groupName)
    {
        $task = self::buildTask($command, $groupName);
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