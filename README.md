yii2-extensions-manager
==========================
Extension that allows you to install, uninstall, activate and deactivate Yii2 or DotPlant extensions right through your web browser.

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
## Module
The extension has been created as a module. To enable access to all features you should configure the module with a name of `extensions-manager` as shown below:
```php
'modules' => [
   'extensions-manager' => [
            'class' => 'DevGroup\ExtensionsManager\ExtensionsManager',
        ],
],
```
**WARNING**
> Extension is now on the development stage. 
> You can use it at your own risk.

## Requirements
TBD
## Usage
TBD
## License
TBD
