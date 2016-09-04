<?php

namespace DevGroup\ExtensionsManager\helpers;

use cebe\markdown\GithubMarkdown;
use DevGroup\DeferredTasks\helpers\DeferredHelper;
use DevGroup\DeferredTasks\helpers\ReportingChain;
use DevGroup\DeferredTasks\helpers\ReportingTask;
use DevGroup\DeferredTasks\models\DeferredGroup;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\models\Extension;
use Packagist\Api\Result\Package\Version;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

class ExtensionDataHelper extends Component
{
    /** @var  string package current version */
    private static $currentVersion;

    /**
     * @param $data
     * @return string
     */
    public static function getType($data)
    {
        $type = '';
        if (true === isset($data['package']['type'])) {
            $type = $data['package']['type'];
        } elseif (true === isset($data['type'])) {
            $type = $data['type'];
        }
        return $type;
    }

    /**
     * @param array $data
     * @param $key
     * @param bool $asArray
     * @return array
     */
    public static function getInstalledExtraData($data, $key, $asArray = false)
    {
        $out = '';
        if (false === empty($data['extra'][$key])) {
            $out = $data['extra'][$key];
        }
        if (true === $asArray) {
            $out = is_array($out) ? $out : (empty($out) ? [] : [$out]);
        } else {
            $out = is_array($out) ? implode(', ', $out) : $out;
        }
        return $out;
    }

    /**
     * Fetching localized data from defined field according to package current version
     * @param $data
     * @param $type
     * @param string $field
     * @return string
     */
    public static function getLocalizedVersionedDataField($data, $type, $field)
    {
        $string = '';
        $langId = Yii::$app->language;
        if (false === empty($data['package']['versions'][self::$currentVersion]['extra'][$type][$field . '_' . $langId])) {
            $string = $data['package']['versions'][self::$currentVersion]['extra'][$type][$field . '_' . $langId];
        } elseif (false === empty($data['package']['versions'][self::$currentVersion]['extra'][$type][$field])) {
            $string = $data['package']['versions'][self::$currentVersion]['extra'][$type][$field];
        } elseif (false === empty($data['package']['versions'][self::$currentVersion][$field])) {
            $string = $data['package']['versions'][self::$currentVersion][$field];
        } elseif (false === empty($data['package'][$field])) {
            $string = $data['package'][$field];
        }
        return $string;
    }

    /**
     * Fetching localized data from defined field from for example installed.json file
     * data who has no 'versions' array inside
     * @param $data
     * @param $type
     * @param string $field
     * @return string
     */
    public static function getLocalizedDataField($data, $type, $field)
    {
        $string = '';
        $langId = Yii::$app->language;
        if (false === empty($data['extra'][$type][$field . '_' . $langId])) {
            $string = $data['extra'][$type][$field . '_' . $langId];
        } elseif (false === empty($data['extra'][$type][$field])) {
            $string = $data['extra'][$type][$field];
        } elseif (false === empty($data[$field])) {
            $string = $data[$field];
        }
        return $string;
    }

    /**
     * Fetching data from defined field according to package current version
     * @param $data
     * @param $key
     * @param bool $asArray
     * @return array
     */
    public static function getOtherPackageVersionedData($data, $key, $asArray = true)
    {
        $out = [];
        if (false === empty($data['package']['versions'][self::$currentVersion][$key])) {
            $out = $data['package']['versions'][self::$currentVersion][$key];
        }
        if (true === $asArray) {
            $out = is_array($out) ? $out : [$out];
        } else {
            $out = is_array($out) ? implode(', ', $out) : $out;
        }
        return $out;
    }

    /**
     * Fetching data from defined field from for example installed.json file
     * data who has no 'versions' array inside
     * @param $data
     * @param $key
     * @param bool $asArray
     * @return array | string
     */
    public static function getOtherPackageData($data, $key, $asArray = false)
    {
        $out = null;
        if (false === empty($data[$key])) {
            $out = $data[$key];
        }
        if (true === $asArray) {
            $out = is_array($out) ? $out : [$out];
        } else {
            $out = is_array($out) ? implode(', ', $out) : $out;
        }
        return $out;
    }

    /**
     * Humanizes github Readme.md file content
     * We adopt github retrieves Readme.md as json string where "content" field contains base64 encoded markdown
     * @param $data
     * @return string
     */
    public static function humanizeReadme($data)
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
    public static function getVersions($packagistVersions, $gitCurrent)
    {
        $versions = [];
        $current = '';
        if (null !== $gitCurrent) {
            if (false === empty($gitCurrent['tag_name'])) {
                $current = $gitCurrent['tag_name'];
            } elseif (false === empty($gitCurrent['name'])) {
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

    /**
     * Prepares migration command
     *
     * @param array $ext
     * @param ReportingChain $chain
     * @param string $way
     * @param $group
     */
    public static function prepareMigrationTask(
        array $ext,
        ReportingChain $chain,
        $way = ExtensionsManager::MIGRATE_TYPE_UP,
        $group
    ) {

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
    public static function buildTask($command, $groupName)
    {
        $groupConfig = [
            'email_notification' => 0,
            'allow_parallel_run' => 0,
            'group_notifications' => 0,
            'run_last_command_only' => 0,
        ];
        if (null === $group = DeferredGroup::findOne(['name' => $groupName])) {
            $group = new DeferredGroup();
            $group->loadDefaultValues();
            $group->setAttributes($groupConfig);
            $group->name = $groupName;
            $group->save();
        }
        if ((int)$group->group_notifications !== 0) {
            // otherwise DeferredController 'deferred-queue-complete' event will not trigger
            // and we'll unable to write config
            $group->setAttributes($groupConfig);
            $group->save(array_keys($groupConfig));
        }
        $task = new ReportingTask();
        $task->model()->deferred_group_id = $group->id;
        $task->cliCommand(DeferredHelper::getPhpBinary(), $command);
        return $task;
    }
}
