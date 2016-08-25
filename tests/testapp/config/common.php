<?php
$vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor';
$params = require(__DIR__ . '/params.php');
return [
    'params' => $params,
    'vendorPath' => $vendorPath,
    'bootstrap' => ['fake-one', 'fake-two', 'fake-three', 'fake-four',],
    'controllerMap' => [
        'extension' => [
            'class' => \DevGroup\ExtensionsManager\commands\ExtensionController::className(),
        ],
    ],
    'aliases' => [
        '@fakedev/FakeOne' => realpath(dirname(__DIR__) . '/vendor/fakedev/yii2-fake-ext/src'),
        '@fakedev/FakeThree' => realpath(dirname(__DIR__) . '/vendor/fakedev/yii2-fake-ext3/src'),
        '@fakedev2/FakeTwo' => realpath(dirname(__DIR__) . '/vendor/fakedev2/yii2-fake-ext2/src'),
        '@fakedev2/FakeFour' => realpath(dirname(__DIR__) . '/vendor/fakedev2/yii2-fake-ext4/src'),
        '@testsHelper' => realpath(dirname(dirname(__DIR__)) . '/testhelper'),
        '@DevGroup/ExtensionsManager' => realpath(dirname(dirname(dirname(__DIR__))) . '/src'),
        '@bower' => $vendorPath . DIRECTORY_SEPARATOR . 'bower-asset',
        '@adminUtils' => $vendorPath . DIRECTORY_SEPARATOR . '/devgroup/yii2-admin-utils/src',
    ],
    'modules' => [
        'extensions-manager' => [
            'class' => \DevGroup\ExtensionsManager\ExtensionsManager::class,
        ],
        'fake-one' => [
            'class' => 'fakedev\FakeOne\FakeOneModule',
        ],
        'fake-two' => [
            'class' => 'fakedev2\FakeTwo\FakeTwoModule',
        ],
        'fake-three' => [
            'class' => 'fakedev\FakeThree\FakeThreeModule',
        ],
        'fake-four' => [
            'class' => 'fakedev2\FakeFour\FakeFourModule',
        ],
    ],
    'components' => [
        'i18n' => [
            'translations' => [
                'extensions-manager' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => dirname(dirname(dirname(__DIR__))) . '/src/messages',
                ]
            ],
        ],
        'filedb' => [
            'class' => 'yii2tech\filedb\Connection',
            'path' => __DIR__ . '/data',
        ],
        'mutex' => [
            'class' => yii\mutex\MysqlMutex::class,
            'autoRelease' => false,
        ],
    ],
];
