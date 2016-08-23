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

После этого необходимо выполнить миграции пакета `devgroup/yii2-deferred-tasks`

```bash
php yii migrate --migrationPath=@DevGroup/DeferredTasks/migrations
```

и указать путь до корневой директории пользователя разделе `params` конфигураций yii-приложения
```php
// ...
'params' => [
    // ...
    'deferred.env' => [
        'HOME' => '/path/to/home',
    ],
    // ...
],
// ...
```

Расширение реализовано в виде модуля, поэтому для его активации необходимо добавить в конфигурационный файл следующий код:

```php
// ...
'modules' => [
    // ...
    'extensions-manager' => [
        'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
    ],
    // ...
],
// ...
```

Далее требуется создать папки для хранения конфигураций. По умолчанию это

- `@app/config/configurables-state`
- `@app/config/generated`

но вы можете изменить их в настройках модуля следующим образом

```php
'modules' => [
    'extensions-manager' => [
        'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
        'configurationUpdater' => [
            'configurablesStatePath' => '@app/config/configurables-state',
            'generatedConfigsPath' => '@app/config/generated',
        ],
    ],
],
```

И последнее - активировать само расширение

```bash
php yii extension/activate devgroup/yii2-extensions-manager
```

Теперь `yii2-extensions-manager` доступен по ссылке по ссылке `/extensions-manager/extensions/index`.

Кроме этого менеджер расширений имеет ряд своих настроек. Настоятельно рекомендуем их изменить перед началом работ

Перейдите на `your-site.com/extensions-manager/extensions/config` и заполните их своими данными:

- Ключ доступа Github API - ваш персональный токен к Github API. Без этого будет доступно выполнение только 60 запросов к API в час.
  Подробнее [здесь](https://developer.github.com/v3/#rate-limiting).   
- Имя приложения Github - смотри [тут](https://developer.github.com/v3/#user-agent-required)
- Путь до composer - ваш системный путо до composer-а.
  Для unix подобных систем вы можете просто выполнить ```which composer``` в консоли, скопировать ркзультат и вставить в поле.
