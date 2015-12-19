# Спецификация пакета yii2-extensions-manager

## Описание

Пакет основан на [yii2-deferred-tasks](https://github.com/DevGroup-ru/yii2-deferred-tasks) и предназначен для установки composer-расширений в приложение.
Контроллеры должны использовать [Combined Action](https://github.com/DevGroup-ru/yii2-admin-utils/blob/master/src/actions/CombinedAction.php) из [yii2-admin-utils](https://github.com/DevGroup-ru/yii2-admin-utils)

Установка пакетов происходит через composer.
Специально для того, чтобы свести к минимуму обновления файла `composer.json` самой CMS - все зависимости выделены в 2 отдельных пакета `devgroup/dotplant` и `devgroup/dotplant-dev`.

## Workflow

- **Extensions->Index** показывает список установленных пакетов.
- **Extensions->Search** показывает список доступных пакетов.
- **Extensions->Show** показывает пакет и:
	* Кнопку **Install**, если пакет не установлен
	* Кнопки **Uninstall**, **Activate**, **Deactivate**, **Update** если установлен
- **Extensions->Install** создает OneTimeTask, который сразу же запускается в фоновом процессе средствами yii2-deferred-tasks.
	* По сути запускается `composer require package-vendor/package-name`, а весь вывод пишется в файл(`command1 >> temporary_log_file 2>&1 &`).
	* На стороне браузера отображается процесс выполнения этого задания(периодический запрос нового содержимого файла).
	* При успешном завершении установки пользователь видит обновлённое представление show по пакету.

## Структура и требования к расширениям

### Общие

META-информация о пакете указывается в секции "extra" и подсекции "yii2-extension":

```json
{
	"name": "devgroup/yii2-extension-example",
	"type": "yii2-extension",
	"description": "Here will be the description of your extension",
	"autoload": {
		"psr-4": {
			"DevGroup\\ExtensionSample\\": "src/"
		}
	},
	"autoload-dev": {
		"psr-4": {
			"DevGroup\\ExtensionSample\\Tests\\": "tests/"
		}
	},
	"extra": {
		"yii2-extension": {
			"name": "Sample extension",
			"name_ru": "Пример расширения",
			"iconUrl": "https://st-1.dotplant.ru/images/ext-sample.png",
			"description_ru": "Это пример описания расширения на русском языке"
		},
		"migrationPath": ["src/migrations/"],
		"bootstrap": "DevGroup\\ExtensionSample\\ExtensionBootstrap",
		"configurables": "src/configurables.php",
		"translationCategory": "extension.sample"
	}
}
```

Описание пакета ищется в первую очередь в `extra.yii2-extension.description_%locale%`, где `%locale%` - текущий язык yii2 приложения.
Вторым по приоритету идет `extra.yii2-extension.description` и последним просто `description`.

Аналогичный принцип и для названия(name) расширения.

**iconUrl** - полный URL до иконки расширения минимальным размером 64x64 px в формате PNG. Крайне желательно использовать протокол HTTPS.

**migrationPath** - путь до миграций расширения, относительно корня расширения.

**configurables** - относительный путь до файла с массивом конфигурируемых объектов расширения.

**translationCategory** - категория сообщений для перевода через `Yii::t`. Если отсутствуют - сообщения не переводятся.

### DotPlant-специфичные

* Расширения обязательно должны быть иметь `"type": "dotplant-extension"` в своём composer.json - это позволяет искать новые пакеты в packagist именно для dotplant, не показывая лишние и несовместимые (dotplant2 использовал тип `dotplant2-extension`).
* Зависимость к мета-пакету `devgroup/dotplant` указывать в расширении **не желательно**.
* Если расширение требовательно к версиям отдельных компонентов dotplant - зависимость надо указывать именно к ним.

> **@todo** подумать на тему категоризации расширений(например: module, theme, blocks pack, bundle, wysiwyg, ???)

## Установка расширений

Со стороны структуры расширения мы имеем 2 сценария:

### Стандартные расширения типа yii2-extension

Имеют bootstrap, который за счёт yii2-composer автоматически цепляется приложением yii2. Применение миграций оставляем на совести пользователя, поскольку расширения не указывают в явном виде, где лежат миграции.

### Расширения типа dotplant-extension

Эти расширения обязаны указывать путь к миграциям. Поэтому принцип их установки разделяется на следующие этапы:

- `composer require package-vendor/package-name` - просто устанавливает расширение и его зависимости. После успешной установки появляется запись в **Extensions**
- Когда composer-пакет установлен его можно активировать. Процесс активации запускается в следующем порядке:
	* Добавляем в `Yii::$app->params['yii.migrations']` пути, указанные в migrationPath composer.json пакета.
	* Применяем все миграции
	* Устанавливаем флаг is_active у расширения
	* Запускаем процесс переконфигурации приложения(перегенерация configurables)
- Процесс деактивации аналогичен:
	* Отменяем миграции расширения
	* Ставим is_active=0
	* Убираем путь до миграций из yii.migrations
	* Запускаем процесс переконфигурации приложения
- Процесс удаления расширения(Uninstall):
	* Деактивировать расширение, если оно активно
	* `composer remove package-vendor/package-name`
	* Удалить запись из Extensions

## Extensions

Extensions по сути из себя представляет php-файл, генерируемый приложением посредством `var_export`.

- **composer_name** - string, required, название composer-пакета. По этому полю пакеты будут искаться контроллерами.
- **composer_type** - string, тип из composer.json
- **is_active** - bool, required, default=0 - активно ли расширение

Остальная информация будет считываться из `vendor/composer/installed.json`.

> **На будущее: ** добавить возможность периодического опроса composer на новые версии и показывать, что есть обновления. Проблема в том, что из-за настроек взаимных зависимостей самая последняя версия расширения может быть запрещена к установке, поэтому проверять надо в рамках текущих условий версионности зависимостей.

## Конфигурационный фреймворк для расширений

Строится на основе Configurables из dotplant2.
Концепт настраиваемых объектов расширяется от модулей к любым объектам.

Перечень конфигурируемых объектов задаётся массивом в файле, указанном в `configurables` файла composer.json расширения.

Пример файла:

```php
return [
	[
		'sectionName' => 'Configuration section name',
		'configurationView' => 'src/views/_configuration.php',
		'configurationModel' => 'DevGroup\ExtensionSample\models\ConfigurationModel',
	]
];
```

В данном формате не описываются какие-то конкретные объекты, которые проходят конфигурацию. Вместо этого происходит описание секций конфигурации со стороны интерфейса.

ConfigurationModel в свою очередь на выходе даёт массив необходимых параметров, которые будут применяться к приложению в различных условиях(web,console,test,common,...).

**sectionName** - переводится в соответствии с `translationCategory` из composer.json пакета расширения. False - не показывать эту секцию в настройках.

### Абстрактный класс BaseConfigurationModel

Все модели конфигурации(configurationModel) должны быть наследованы от абстрактного класса BaseConfigurationModel.

За основу класса берётся соответствующий класс из dotplant2: [BaseConfigurationModel](https://github.com/DevGroup-ru/dotplant2/blob/master/application/modules/config/models/BaseConfigurationModel.php).

`$module` заменяется на `$configurableObject`, при этом добавляется свойство `public $isModule = false;`. Значение - id объекта в Application.

Вместо getModuleInstance теперь getConfigurableObjectInstance:

```php

	/**
	 * @return null|\yii\base\Module|\yii\base\Object
	 */
	public function getConfigurableObjectInstance()
	{
		if ($this->isModule) {
			return Yii::$app->getModule($this->configurableObject);
		} else {
			return Yii::$app->get($this->configurableObject);
		}
	}
```

Остальные принципы - те же.

> **Важно: ** Всем расширениям желательно поддерживать i18n, соответственно хотя бы один из их ConfigurationModel должен дописывать в common-конфиг информацию о категории переводов.

## Необходимые доработки на стороне yii2-deferred-tasks
### Перенаправление вывода во временный файл. 

Файл должен располагаться в runtime директории. Информация о лог-файле должна присутствовать в таске.

### Realtime reporting task

Это как раз выполнение задачи в фоне с отображение результата в браузере.
При этом у данного функционала должно быть 2 режима работы:

* **runImmediate=true** - при создании таска в рамках текущего запроса(пользователь кликнул install) - запускается detached процесс(свободный амперсанд на конце команды)
* **runImmediate=false** - таск запустится первым при следующей отработке cron-таска отложенных задач, т.е. пользователь ждёт не более минуты(если крон каждую минуту), браузер запрашивает статус таска, когда таск запустится - в браузер начнёт поступать вывод комманды

Таким образом необходимо реализовать дополнительный JS-класс(**DeferredReportingTaskRunner**), который будет работать по следующему сценарию:

1. Конструктор делает ajax-запрос на запуск таска по его id
2. Получает мета-информацию, содержащую:
	- **status** - статус таска(запустили, уже запущен, поставлен в очередь, закончились)
	- **error** - true - произошла, false - всё ок
	- **errorMessage** - сообщение об ошибке
	- **lastFseekPosition** - сдвиг прочтения файла, при первом запросе=0
	- **newOutput** - новый вывод, при первом запросе пустой
	- **taskStatusCode** - exit-код процесса, 0 - всё хорошо, null - процесс ещё не завершился или не запускался
3. Если ошибки не произошло - открывает модальное окно (BootstrapModalNotifier) с содержимым:
	* Надпись "Задание поставлено в очередь", если статус именно такой(он будет, если `runImmediate==false`)
	* Галочка внизу "Закрыть это окно после успешного завершения задания", по-умолчанию - ВКЛ
	* Поле с выводом команды задачи, пока таск не запущен - крутилка-спиннер.
4. Каждые N-миллисекунд(*параметр должен быть настраиваемым*) делает запрос на сервер в контроллер ReportingTask, передавая ему id-таска и последний `lastFseekPosition`. Контроллер в ответ открывает лог-файл задачи(если она запустилась), перемещает позицию через fseek на `lastFseekPosition` и читает весь вывод до конца. На выходе - та же мета-информация, что и в п.2, но уже с новым выводом команды.

Соответственно в самом запускателе задач по завершению процесса - в базу должен писаться taskStatusCode.


