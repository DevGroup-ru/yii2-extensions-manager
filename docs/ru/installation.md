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

## Настройка devgroup/yii2-deferred-tasks

После этого необходимо выполнить миграции пакета `devgroup/yii2-deferred-tasks`

```bash
php yii migrate --migrationPath=@DevGroup/DeferredTasks/migrations
```

указать путь до корневой директории пользователя разделе `params` конфигураций yii-приложения

```php
// ...
'params' => [
    // ...
    'deferred.env' => [ // все указанные здесь поля будут установлены в виде параметров окружения для выполняемого скрипта
        'HOME' => '/path/to/home', // путь до домашней директории. Необходим для корректной работы composer-а
    ],
    // ...
],
// ...
```

и добавить компонент мьютекса для консольного приложения

```php
'components' => [
    // ...
    'mutex' => [
        'class' => 'yii\mutex\MysqlMutex', // в большинстве случаев это MySQL
        'autoRelease' => false,
    ],
    // ..
],
```

## Настройка devgroup/yii2-extensions-manager

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

Для автоматического обноаления настроек придется модифицировать все файлы в директории `config` следующим образом.

#### `config/console.php`

```php
<?php

use yii\helpers\ArrayHelper;

$config = [/* Your configuration*/];

$filename = __DIR__ . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'common-generated.php';
if (file_exists($filename)) {
    $config = ArrayHelper::merge(require($filename), $config);
}
$filename = __DIR__ . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'console-generated.php';
if (file_exists($filename)) {
    $config = ArrayHelper::merge($config, require($filename));
}

return $config;
```

#### `config/params.php`

```php
<?php

use yii\helpers\ArrayHelper;

$config = [/* Your params*/];

$filename = __DIR__ . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'params-generated.php';
if (file_exists($filename)) {
    $config = ArrayHelper::merge($config, require($filename));
}

return $config;
```

#### `config/web.php`

```php
<?php

use yii\helpers\ArrayHelper;

$config = [/* Your configuration*/];

$filename = __DIR__ . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'common-generated.php';
if (file_exists($filename)) {
    $config = ArrayHelper::merge(require($filename), $config);
}
$filename = __DIR__ . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'web-generated.php';
if (file_exists($filename)) {
    $config = ArrayHelper::merge($config, require($filename));
}

return $config;
```

И последнее - активировать само расширение

```bash
php yii extension/activate devgroup/yii2-extensions-manager
```

Теперь `yii2-extensions-manager` доступен по ссылке по ссылке `your-site.com/extensions-manager/extensions/index`.

Кроме этого менеджер расширений имеет ряд своих настроек. Настоятельно рекомендуем их изменить перед началом работ

Перейдите на `your-site.com/extensions-manager/extensions/config` и заполните их своими данными:

- Ключ доступа Github API - ваш персональный токен к Github API. Без этого будет доступно выполнение только 60 запросов к API в час.
  Подробнее [здесь](https://developer.github.com/v3/#rate-limiting).   
- Имя приложения Github - смотри [тут](https://developer.github.com/v3/#user-agent-required)
- Путь до composer - ваш системный путо до composer-а.
  Для unix подобных систем вы можете просто выполнить `which composer` в консоли (Для windows систем - выполнить `echo %HOMEPATH%`), скопировать ркзультат и вставить в поле.
