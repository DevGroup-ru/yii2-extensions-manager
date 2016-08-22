Установка
=========

Предпочтителен вариант установки через [композер](http://getcomposer.org/download/).

Для этого выполните команду

```bash
php composer.phar require --prefer-dist devgroup/yii2-extensions-manager "*"
```

или добавьте в секцию `require` вашего `composer.lock` следующую строку

```bash
"devgroup/yii2-extensions-manager": "*"
```

После этого необходимо выполнить миграции:

```bash
/usr/bin/php yii migrate --migrationPath=@DevGroup/DeferredTasks/migrations
```

Расширение создано в виде модуля, поэтому для его активации необходимо добавить в конфигурационный файл следующий код:

```php
'modules' => [
   'extensions-manager' => [
            'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
        ],
],
```

Теперь расширение доступно по ссылке по ссылке `/extensions-manager/extensions/index`.
