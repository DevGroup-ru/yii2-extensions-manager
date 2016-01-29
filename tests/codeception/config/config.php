<?php
/**
 * Application configuration shared by all test types
 */
return [
    'language' => 'ru',
    'controllerMap' => [
//        'fixture' => [
//            'class' => 'yii\faker\FixtureController',
//            'fixtureDataPath' => '@tests/codeception/fixtures',
//            'templatePath' => '@tests/codeception/templates',
//            'namespace' => 'tests\codeception\fixtures',
//        ],
    ],
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'extensions-manager' => [
            'class' => DevGroup\ExtensionsManager\ExtensionsManager::className(),
        ],
    ]
];