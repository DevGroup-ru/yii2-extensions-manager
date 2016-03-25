<?php
$config = [
    'id' => 'minimal',
    'basePath' => dirname(__DIR__),
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@DevGroup/ExtensionsManager/views' => dirname(dirname(dirname(__DIR__))) . '/src/views',
                    '@app/views' => dirname(__DIR__) . '/views',
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'bVtVlPjifiJ5Y_ZDHEVkerVqYIW6Xc8w',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],

];
$common = include 'common.php';
return \yii\helpers\ArrayHelper::merge($config, $common);
