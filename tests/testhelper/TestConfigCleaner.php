<?php
namespace testsHelper;

class TestConfigCleaner
{
    public static function cleanTestConfigs()
    {
        $files = self::prepare();
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public static function removeExtFile()
    {
        $fn = realpath(dirname(__DIR__) . '/config/extensions.php');
        if (true === file_exists($fn)) {
            unlink($fn);
        }
    }

    public static function checkExtFile()
    {
        $fn = realpath(dirname(__DIR__) . '/config/extensions.php');
        return file_exists($fn);
    }

    public static function cleanExtensions()
    {
        self::removeExtFile();
        copy(
            dirname(__DIR__) . '/data/extensions.php',
            dirname(__DIR__) . '/config/extensions.php'
        );

    }

    public static function checkIsset()
    {
        return 0 < count(self::prepare());
    }

    private static function prepare()
    {
        $pathStates = realpath(dirname(__DIR__) . '/config/configurables-state/');
        $pathGenerated = realpath(dirname(__DIR__) . '/config/generated/');
        $statesFiles = glob($pathStates . "/*.php");
        $generatedFiles = glob($pathGenerated . "/*.php");
        $files = array_merge($statesFiles, $generatedFiles);
        return $files;
    }
}