Создание расширения для DotPlant CMS
====================================

[DotPlant CMS](http://dotplant.ru/) начиная с третьей версии умеет работать с двумя типами расширений:

- yii2-extension
- dotplant-extension

Поговорим про создание пакета типа dotplant-extension.

## Создание скелета расширения

Проще всего взять за основу демонстрационный пакет. Для этого клонируем его себе на компьютер, переходим в директорию с ним и удаляем каталог `.git`.

```bash
git clone https://github.com/DevGroup-ru/dotplant-extension-demo.git
cd dotplant-extension-demo
rm -rf .git
```

В первую очередь поправим файл `composer.json`. Ниже его содержимое с комментариями.

```jsonp
{
    "name": "vendor-name/extension-name", // название расширения
    "description": "There is an extension description here", // описание расширения
    "type": "dotplant-extension", // тип расширения. Оставляем как есть
    "keywords": ["yii2", "dotplant", "new-tag", "another-tag"], // список тегов для поиска
    "license": "MIT", // тип лицензии
    "minimum-stability": "stable", // минимальная стабильность (dev или stable)
    "authors": [ // список авторов пакета
        {
            "name": "Vendor Full Name",
            "email": "vendor@email.here"
        }
    ],
    "require": { // зависимости расширения
        "php": ">=5.5.0",
        "yiisoft/yii2": "~2.0.0",
        "another-vendor/required-package": "~1.2.3"
    },
    "require-dev": { // дополнительные зависимости для разработки. Чаще всего это пакеты тестирования. Например, codeception
        "another-vendor/required-dev-package": "^2.3.4"
    },
    "autoload": { // правила для автозагрузчика
        "psr-4": {
            "VendorNameSpace\\ExtensionNameSpace\\": "src/" // Указание соответствия нэймспейсов и директорий репозитория
        }
    },
    "extra": { // дополнительные сведения
        "yii2-extension": {
            "name": "Package name", // название пакета
            "name_ru": "Название пакета на нусском языке", // Для другого языка просто добавляем соответствующий суффикс
            "iconUrl": "", // полный URL до иконки расширения минимальным размером 64x64 px в формате PNG. Крайне желательно использовать протокол HTTPS
            "description_ru": "Описание пакета на русском языке" // Для другого языка просто добавляем соответствующий суффикс
        },
        "migrationPath": [ // относительные пути от корня расширения до директории с миграциями
            "src/migrations"
        ],
        "configurables": "src/configurables.php", // сведения для раздела настроек менеджера расширений extensions-manager
        "translationCategory": "my-awesome-dotplant-extension" // категория сообщений для перевода через Yii::t. Если отсутствуют - сообщения не переводятся.
    }
}
```

### Особенности composer.json

Все вышеописанние является стандартным набором данных для yii2-extension. Исключение - секция `extra`.
Она описывает специфицные поля для yii2-extensions-manager.

- Расширения обязательно должны быть иметь `"type": "dotplant-extension"` в своём `composer.json` - это позволяет искать новые пакеты в packagist именно для dotplant, не показывая лишние и несовместимые (dotplant2 использовал тип `dotplant2-extension`);
- Зависимость к мета-пакету `devgroup/dotplant` указывать в расширении **не желательно**;
- Если расширение требовательно к версиям отдельных компонентов dotplant - зависимость надо указывать именно к ним. При этом, чем шире набор поддерживаемых версий - тем лучше.


## Рекомендуемая файловая структура

В корне репозитория рекомендуется хранить только базовые файлы

- `.gitignore` - исключения для git
- `.travis.yml` - конфигурации для сервиса `travis-ci.org`
- `LICENSE` - текст лицензии
- `README.md` - краткое описание расширения
- `composer.json` - мета-файл для composer
- `composer.lock` - фиксированные версии пакетов зависимостей
- `codeception.yml` - настройки Codeception
- `phpunit.xml` - файл конфигураций PHPUnit

Все остальное желательно вынести в поддиректории

- `docs` - документация с разделением на различные языви
    - ...
    - `de`
    - `en`
    - `ru`
    - ...
- `src` - исходные файлы. Расширение для dotplant - это обычный модуль приложения на Yii2. Поэтому оно может хранить котроллеры, модели, миграции и прочее.
    - ...
    - `controllers`
    - `models`
    - `views`
    - ...
- `tests` - тесты

### Специфичные файлы

Модель конфигураций расширения (обычно `src/models/Configuration.php`) должна наследоваться от `DevGroup\ExtensionsManager\models\BaseConfigurationModel`. Ниже пример файла с комментариями

```php
<?php

namespace VendorNameSpace\ExtensionNameSpace\models;

use VendorNameSpace\ExtensionNameSpace\Module;
use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use Yii;

class Configuration extends BaseConfigurationModel
{
    /**
     * Полное имя класса модуля
     */
    public function getModuleClassName()
    {
        return '\VendorNameSpace\ExtensionNameSpace\Module';
    }

    /**
     * Правила валидации полей модели
     */
    public function rules()
    {
        return [
            [['someProperty'], 'boolean'],
        ];
    }

    /**
     * Заголовки для полей
     */
    public function attributeLabels()
    {
        return [
            'someProperty' => Yii::t('my-awesome-dotplant-extension', 'Some property'),
        ];
    }

    /**
     * Массив настроек для web-приложения (равносильно указанию настроект в файл `config/web.php`)
     */
    public function webApplicationAttributes()
    {
        return [];
    }

    /**
     * Массив настроек для консольного приложения (равносильно указанию настроект в файл `config/console.php`)
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Массив общих настроек доступных, как в консольном приложении, так и в web (равносильно указанию настроект в файл `config/common.php`)
     */
    public function commonApplicationAttributes()
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'my-awesome-dotplant-extension' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'messages',
                        ]
                    ]
                ],
            ],
            'modules' => [
                'moduleName' => [ // указываем имя модуля
                    'class' => Module::class, // полное название класса модуля
                    // ниже настройки модуля
                    'someProperty' => (bool) $this->someProperty,
                ]
            ],
        ];
    }

    /**
     * Массив параметров (равносильно указанию настроект в файл `config/params.php`)
     */
    public function appParams()
    {
        return [];
    }

    /**
     * Массив псевдонимов (равносильно указанию настроект в файл `config/aliases.php`)
     */
    public function aliases()
    {
        return [
            '@VendorNameSpace/ExtensionNameSpace' => realpath(dirname(__DIR__)),
        ];
    }
}
```

Файл описания раздела конфигурирования для менеджера расщирения (Обычно `src/configurables.php`)

```php
<?php
return [
    [
        'sectionName' => 'My awesome extension', // Название вкладки в разделе конфигурирования
        'configurationView' => 'src/views/_configuration.php', // Файл формы представления
        'configurationModel' => \VendorNameSpace\ExtensionNameSpace\models\Configuration::class, // Класс модели конфигураций приложения
    ],
];
```

Файл представления формы редактирования параметров расширения (Обычно `src/views/_configuration.php`).

```php
<?php
/**
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var \yii\web\View $this
 */
?>

<div class="box-body">
    <?= $form->field($model, 'someProperty')->checkbox() ?>
</div>
```

## Публикация расширения

1. Создать локальный git-репозиторий для расширения;
2. Загрузить его на `github.com`;
3. Опубликовать пакет на `packagist.org`;
4. Настроить хук автообновления версии пакета для удобства (Опционально).
