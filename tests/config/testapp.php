<?php
return [
    'id' => 'testapp',
    'basePath' => dirname(__DIR__),
    'vendorPath' => '../../../vendor',
    'controllerMap' => [
        'extension' => [
            'class' => \DevGroup\ExtensionsManager\commands\ExtensionController::className(),
        ],
    ],
    'components' => [
//        'db' => [
//            'class' => yii\db\Connection::className(),
//            'dsn' => 'mysql:host=localhost;dbname=yii2_extensions_manager',
//            'username' => 'root',
//            'password' => 'winston',
//        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
    'modules' => [
        'extensions-manager' => [
            'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
        ],
    ],
];