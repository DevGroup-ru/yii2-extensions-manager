Как это работает
================

Ниже описан процесс работы extension manager-а с расширениями.

### Стандартные расширения типа yii2-extension

Имеют bootstrap, который за счёт [yii2-composer](https://github.com/yiisoft/yii2-composer) автоматически цепляется приложением yii2.
Применение миграций в данном случае остается на совести пользователя, поскольку расширения не указывают в явном виде, где лежат миграции.

Для стандартизации механизма применения миграций расширений yii2 мы предлагаем использовать тот же синтаксис, что и для расширений типа `dotplant-extension`.

Для этого в секцию extra файла composer.json расширения необходимо добавить путь к миграциям `migrationPath` относительно папки расширения, например:

```json
{
  "extra": {
      "migrationPath": ["src/migrations/"],
      "bootstrap": "Vendor\\Package\\YourYii2Bootstrap"
  }
}
```

### Расширения типа dotplant-extension

Эти расширения **обязаны** указывать путь к миграциям. Поэтому принцип их установки разделяется на следующие этапы:

- Выполнение команды `php composer.phar require package-vendor/package-name --working-dir=/path/to/application/extensions` - просто устанавливает расширение и его зависимости. После успешной установки появляется запись на странице `Extensions`
- Когда composer-пакет установлен его можно активировать. Процесс активации запускается в следующем порядке:
    - Добавление в `Yii::$app->params['yii.migrations']` путей, указанных в migrationPath composer.json пакета. Это можно сделать с помощью `BaseConfigurationModel::appParams`
    - Примение всех миграций
    - Устанавка флага `is_active=1` у расширения
    - Запуск процесса переконфигурации приложения (перегенерация configurables)
- Процесс деактивации аналогичен:
    - Отмена миграциё расширения
    - Устанка флага `is_active=0` у расширения
    - Удаление путей до миграций из `Yii::$app->params['yii.migrations']`
    - Запуск процесса переконфигурации приложения
- Процесс удаления расширения (Uninstall):
    - Деактивация расширения, если оно активно
    - Выполнение воманды `php composer.phar remove package-vendor/package-name --working-dir=/path/to/application/extensions`
    - Удаление записи из `Extensions`

## Зависимости

#### `wikimedia/composer-merge-plugin`

Yii2 extensions manager успользует composer-пакет [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin).
Он позволяет работать с несколькими `composer.json` файлами одновременно.
Это позволяет не модифицировать `composer.json` и `composer.lock` вашего приложения, а значит исключает возможность конфликта при выполнении `git pull`.
Основные работы производятся с локальными файлами, путь до которых задается в `ExtensionsManager::$localExtensionsPath` (по умолчанию `@app/extensions`).

#### `devgroup/yii2-deferred-tasks`

[Данный пакет](https://github.com/DevGroup-ru/yii2-deferred-tasks) позволяет выполнять команды в фоне с перенаправлением всего вывода в файл.
Он используется при установке, удалении, активации и деактивации расширений.
При выполнение этих операций пользователь может наблюдать за текущим состоянием процесса в модальном окне.

#### `dmstr/yii2-migrate-command`

Для более удобного управления миграциями мы рекомендуем использовать пакет [dmstr/yii2-migrate-command](https://github.com/dmstr/yii2-migrate-command) и выставлять параметр модуля `ExtensionsManager::$autoDiscoverMigrations` в `true`.
Таким образом, все миграции всех расширений будут автоматически добавляться в область видимости `yii2-migrate-command`.

## Ограничения

Некоторые операции менеджера требуют обращения к github API, который имее [ограничение](https://developer.github.com/v3/#rate-limiting) - 60 запросов в час.
Для снятия этого лимита необходимо указать свой `access token` в настройках менеджера расширений.

Для корректной работы необходима версия пакета `dmstr/yii2-migrate-command` более или равная 0.3.1.
