<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ArrayDataProvider $dataProvider
 * @codeCoverageIgnore
 */

use yii\grid\GridView;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\models\Extension;
use kartik\icons\Icon;
use yii\helpers\Html;

\DevGroup\ExtensionsManager\assets\AdminBundle::register($this);
\DevGroup\DeferredTasks\assets\AdminBundle::register($this);
\kartik\icons\FontAwesomeAsset::register($this);

$nameBlockTpl = <<<TPL
<div class="panel panel-default box box-solid">
    <div class="panel-heading ext-list-description box-header with-border">
        <h3 class="panel-title box-title">
            <a data-toggle="collapse" data-target="#%4\$s" class="collapsed" >%s</a>
        </h3>
    </div>
    <div id="%4\$s" class="panel-collapse collapse">
        <div class="panel-body">%s</div>
    </div>
    <div class="btn-group ext-buttons">%s</div>
</div>
TPL;
$gridTpl = <<<TPL
<div class="box-body no-padding">
    {items}
</div>
<div class="box-footer">
    <div class="row ext-bottom">
        <div class="col-sm-5">
            {summary}
        </div>
        <div class="col-sm-7">
            {pager}
        </div>
    </div>
</div>
TPL;

$composerSet = ComposerInstalledSet::get();
$this->title = Yii::t('extensions-manager', 'Installed extensions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-3">
        <div class="configuration-navigation box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list-alt"></i>
                    <?= Yii::t('extensions-manager', 'Extensions manager') ?>
                </h3>
            </div>
            <div class="box-body">
                <?=
                \yii\bootstrap\Nav::widget([
                    'items' => ExtensionsManager::navLinks(),
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                ])
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="extensions-controller__list-extensions box box-solid">
            <div class="box-header clearfix">
                <h3 class="box-title pull-left">
                    <?= Yii::t('extensions-manager', 'Installed extensions') ?>
                </h3>
            </div>
        <?= GridView::widget([
            'id' => 'extensions-list',
            'dataProvider' => $dataProvider,
            'layout' => $gridTpl,
            'tableOptions' => [
                'class' => 'table table-bordered table-hover table-responsive',
            ],
            'pager' => [
                'options' => [
                    'class' => 'pagination pull-right',
                ]
            ],
            'columns' => [
                [
                    'label' => Yii::t('extensions-manager', 'Name'),
                    'attribute' => 'composer_name',
                    'content' => function ($data) use ($nameBlockTpl, $composerSet) {
                        $name = ExtensionDataHelper::getLocalizedDataField(
                            $composerSet->getInstalled($data["composer_name"]),
                            Extension::TYPE_YII,
                            'name'
                        );
                        $description = ExtensionDataHelper::getLocalizedDataField(
                            $composerSet->getInstalled($data["composer_name"]),
                            Extension::TYPE_YII,
                            'description'
                        );
                        $activateButton = (0 == $data['is_active']) ? (
                            Yii::$app->user->can('extensions-manager-activate-extension')
                                ? Html::button(
                                    Yii::t('extensions-manager', 'Activate'),
                                    [
                                        'class' => 'btn btn-success btn-xs',
                                        'data-action' => 'run-ext-task',
                                        'data-ext-task' => ExtensionsManager::ACTIVATE_DEFERRED_TASK,
                                        'data-package-name' => $data["composer_name"],
                                    ]
                                )
                                : ''
                            )
                            : (
                                Yii::$app->user->can('extensions-manager-deactivate-extension')
                                    ? Html::button(
                                        Yii::t('extensions-manager', 'Deactivate'),
                                        [
                                            'class' => 'btn btn-warning btn-xs',
                                            'data-action' => 'run-ext-task',
                                            'data-ext-task' => ExtensionsManager::DEACTIVATE_DEFERRED_TASK,
                                            'data-package-name' => $data["composer_name"],
                                        ]
                                    )
                                    : ''
                            );
                        $removeButton = $data['is_core'] == 0 && Yii::$app->user->can('extensions-manager-uninstall-extension') ?
                            Html::button(
                                Yii::t('extensions-manager', 'Uninstall'),
                                [
                                    'class' => 'btn btn-danger btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::UNINSTALL_DEFERRED_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ]
                            )
                            : '';
                        $buttons = $removeButton
                            . Html::button(
                                Yii::t('extensions-manager', 'Check updates'),
                                [
                                    'class' => 'btn btn-warning btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::CHECK_UPDATES_DEFERRED_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ]
                            )
                            . Html::button(
                                Yii::t('extensions-manager', 'Details') .
                                '  ' . Icon::show('refresh fa-spin', ['style' => 'display: none;'], 'fa'),
                                [
                                    'class' => 'btn btn-info btn-xs',
                                    'data-package-name' => $data["composer_name"],
                                    'data-action' => 'ext-info'
                                ]
                            )
                            . $activateButton;
                        return sprintf($nameBlockTpl, $name, $description, $buttons, str_replace(['\\', '/'], '', $data["composer_name"]));
                    }
                ],
                [
                    'label' => Yii::t('extensions-manager', 'Version'),
                    'content' => function ($data) use ($composerSet) {
                        return ExtensionDataHelper::getLocalizedDataField(
                            $composerSet->getInstalled($data["composer_name"]),
                            Extension::TYPE_YII,
                            'version'
                        );
                    },
                    'options' => [
                        'width' => '150px',
                    ]
                ],
                [
                    'label' => Yii::t('extensions-manager', 'Type'),
                    'attribute' => 'composer_type',
                    'content' => function ($data) {
                        $types = Extension::getTypes();
                        return isset($types[$data['composer_type']])
                            ? $types[$data['composer_type']]
                            : Yii::t('extensions-manager', 'Undefined extension type');
                    },
                    'options' => [
                        'width' => '150px',
                    ]

                ],
                [
                    'label' => Yii::t('extensions-manager', 'Active'),
                    'attribute' => 'is_active',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asBoolean($data["is_active"]);
                    },
                    'options' => [
                        'width' => '150px',
                    ]
                ]
            ],
        ]) ?>
        </div>
    </div>
</div>