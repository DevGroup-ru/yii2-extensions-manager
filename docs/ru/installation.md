Установка
=========

Предпочтителен вариант установки через [composer](http://getcomposer.org/download/).

Для этого выполните команду:

```bash
php composer.phar require --prefer-dist devgroup/yii2-extensions-manager "*"
```

или добавьте в секцию `require` вашего `composer.json` следующую строку

```bash
"devgroup/yii2-extensions-manager": "*"
```

После этого необходимо выполнить миграции:

```bash
php yii migrate --migrationPath=@DevGroup/DeferredTasks/migrations
```

Расширение реализовано в виде модуля, поэтому для его активации необходимо добавить в конфигурационный файл следующий код:

```php
'modules' => [
    'extensions-manager' => [
        'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
    ],
],
```

Теперь расширение доступно по ссылке по ссылке `/extensions-manager/extensions/index`.
