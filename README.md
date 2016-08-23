[![Build Status](https://travis-ci.org/DevGroup-ru/yii2-extensions-manager.svg?branch=master)](https://travis-ci.org/DevGroup-ru/yii2-extensions-manager)
[![codecov.io](https://codecov.io/github/DevGroup-ru/yii2-extensions-manager/coverage.svg?branch=master)](https://codecov.io/github/DevGroup-ru/yii2-extensions-manager?branch=master)

yii2-extensions-manager
=======================
Extension that allows you to install, uninstall, activate and deactivate Yii2 or DotPlant extensions right through your web browser.

## Documentation

- [Russian](docs/ru/README.md)
- English

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist devgroup/yii2-extensions-manager "*"
```

or add

```
"devgroup/yii2-extensions-manager": "*"
```

After it you should execute migrations:

```bash
/usr/bin/php yii migrate --migrationPath=@DevGroup/DeferredTasks/migrations
```

This extension is a yii module because you should add the next code to your configuration file for an activation:

```php
'modules' => [
   'extensions-manager' => [
            'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
        ],
],
```

Now the extension as available by route `/extensions-manager/extensions/index`.

**WARNING**
> Extension is now on the development stage. 
> You can use it at your own risk.

**IMPORTANT**
> You have to have correct version of the [migrate controller](https://github.com/dmstr/yii2-migrate-command)
> equal or above 0.3.1. And double check  ```MigrateController::getMigrationHistory()``` method supports 
> ```MigrateController::$disableLookup``` property

## Usage

Extensions manager has several options. It is strongly recommended to configure them, before you start.

Go to your-site.com/extensions-manager/extensions/config and fill fields with your own values:
- Github API access token - your personal Github API token. Without it you will be able to process only up to 
  60 requests per hour [see](https://developer.github.com/v3/#rate-limiting).   
- Github application name - [see](https://developer.github.com/v3/#user-agent-required)
- Path to Composer - your system path to composer. For Unix-like operating system you can simply run 
  ```which composer``` in console and copy/paste output to this field

Other fields you can leave with default values:
 - Packagist URL
 - Github API URL
 - Extensions storage
 - Extensions per page
 - Verbose output
 
### Console commands

Each command can be run with standard `./yii` command:

#### `extension/activate`

Activates extension by it's composer name.
Example: 
```
./yii extension/activate devgroup/yii2-media-storage
```

#### `extension/deactivate`

Deactivates extension by it's composer name. 

#### `extension/update-config`

Updates config.
Calculates differences between `@vengor/composer/installed.json` and `ExtensionsManager::$extensionsStorage`
and writes new `ExtensionsManager::$extensionsStorage`.
That should be done when you are out of sync and you don't see your extension in list.

#### `extension/list`

Show the list of all installed extensions, it's active state and composer package type.


## Dependencies
TBD