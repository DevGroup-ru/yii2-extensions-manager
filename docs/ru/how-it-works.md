Как это работает
================

Yii2 extensions manager успользует пакет [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin).
Он позволяет работать с несколькими `composer.json` файлами одновременно.
Это позволяет не модифицировать `composer.json` и `composer.lock` вашего приложения, а значит исключает возможность конфликта при выполнении `git pull`.
Основные работы производятся с локальными файлами, путь до которых задается в `ExtensionsManager::$extensionsStorage` (по умолчанию `@app/config/extensions.php`).

Ниже описан процесс работы extension manager-а с расширениями.

### Стандартные расширения типа yii2-extension

Имеют bootstrap, который за счёт yii2-composer автоматически цепляется приложением yii2.
Применение миграций в данном случае остается на совести пользователя, поскольку расширения не указывают в явном виде, где лежат миграции.

### Расширения типа dotplant-extension

Эти расширения обязаны указывать путь к миграциям. Поэтому принцип их установки разделяется на следующие этапы:

- Выполнение команды `/usr/bin/php composer.phar require package-vendor/package-name --working-dir=/path/to/application/extensions` - просто устанавливает расширение и его зависимости. После успешной установки появляется запись на странице `Extensions`
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
    - Выполнение воманды `/usr/bin/php composer.phar remove package-vendor/package-name --working-dir=/path/to/application/extensions`
    - Удаление записи из `Extensions`
