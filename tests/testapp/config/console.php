<?php

$config = [
    'id' => 'minimal-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
];
$common = include 'common.php';
return \yii\helpers\ArrayHelper::merge($config, $common);