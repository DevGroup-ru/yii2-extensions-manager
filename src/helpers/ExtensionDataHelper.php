<?php

namespace DevGroup\ExtensionsManager\helpers;

use cebe\markdown\GithubMarkdown;
use Packagist\Api\Result\Package\Version;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

class ExtensionDataHelper extends Component
{
    /** @var  string package current version */
    private static $_currentVersion;

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
        if (false === empty($data['package']['versions'][self::$_currentVersion]['extra'][$type][$field . '_' . $langId])) {
            $string = $data['package']['versions'][self::$_currentVersion]['extra'][$type][$field . '_' . $langId];
        } elseif (false === empty($data['package']['versions'][self::$_currentVersion]['extra'][$type][$field])) {
            $string = $data['package']['versions'][self::$_currentVersion]['extra'][$type][$field];
        } elseif (false === empty($data['package']['versions'][self::$_currentVersion][$field])) {
            $string = $data['package']['versions'][self::$_currentVersion][$field];
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
        if (false === empty($data['package']['versions'][self::$_currentVersion][$key])) {
            $out = $data['package']['versions'][self::$_currentVersion][$key];
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
        self::$_currentVersion = $current;
        return $versions;
    }
}
