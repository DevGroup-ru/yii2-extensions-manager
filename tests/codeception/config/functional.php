<?php
$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;
/**
 * Application configuration for functional tests
 */
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../../../../config/web.php'),
    require(__DIR__ . '/config.php'),
    [
        'components' => [
            'request' => [
                // it's not recommended to run functional tests with CSRF validation enabled
                'enableCsrfValidation' => false,
            ],
            'urlManager' => [
                'showScriptName' => false,
            ],
        ],
    ]
);
$config['components']['cache'] = [
    'class' => 'yii\caching\DummyCache',
];
return $config;