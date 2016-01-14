<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ArrayDataProvider $dataProvider
 */

use yii\grid\GridView;
use DevGroup\ExtensionsManager\helpers\ExtensionDataHelper;
use DevGroup\ExtensionsManager\components\ComposerInstalledSet;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\models\Extension;
use kartik\icons\Icon;
use yii\helpers\Html;

$nameBlockTpl = <<<TPL
<div class="box box-default collapsed-box">
    <div class="box-header">
        <h3 class="box-title">%s</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">%s</div>
</div>
<div class="btn-group ext-buttons">%s</div>
TPL;

?>

<section>
    <div class="extensions-controller__list-extensions box">
        <?= GridView::widget([
            'id' => 'extensions-list',
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => [
                'class' => 'table table-bordered table-hover dataTable',
            ],
            'columns' => [
                [
                    'label' => Yii::t('extensions-manager', 'Name'),
                    'content' => function ($data) use ($nameBlockTpl) {
                        $name = ExtensionDataHelper::getLocalizedDataField(
                            ComposerInstalledSet::get()->getInstalled($data["composer_name"]),
                            $data["composer_type"],
                            'name'
                        );
                        $description = ExtensionDataHelper::getLocalizedDataField(
                            ComposerInstalledSet::get()->getInstalled($data["composer_name"]),
                            $data["composer_type"],
                            'description'
                        );
                        $activateButton = (0 == $data['is_active']) ?
                            Html::button(Yii::t('extensions-manager', 'Activate'),
                                [
                                    'class' => 'btn btn-success btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::ACTIVATE_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ])
                            : Html::button(Yii::t('extensions-manager', 'Deactivate'),
                                [
                                    'class' => 'btn btn-warning btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::DEACTIVATE_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ]);
                        $buttons = Html::button(Yii::t('extensions-manager', 'Uninstall'),
                                [
                                    'class' => 'btn btn-danger btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::UNINSTALL_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ])
                            . Html::button(Yii::t('extensions-manager', 'Check updates'),
                                [
                                    'class' => 'btn btn-info btn-xs',
                                    'data-action' => 'run-ext-task',
                                    'data-ext-task' => ExtensionsManager::CHECK_UPDATES_TASK,
                                    'data-package-name' => $data["composer_name"],
                                ])
                            . Html::button(Yii::t('extensions-manager', 'Details') .
                                '  ' . Icon::show('refresh fa-spin', ['style' => 'display: none;']),
                                [
                                    'class' => 'btn btn-info btn-xs',
                                    'data-package-name' => $data["composer_name"],
                                    'data-action' => 'ext-info'
                                ])
                            . $activateButton;
                        return sprintf($nameBlockTpl, $name, $description, $buttons);
                    }
                ],
                [
                    'label' => Yii::t('extensions-manager', 'Version'),
                    'content' => function ($data) {
                        return ExtensionDataHelper::getLocalizedDataField(
                            ComposerInstalledSet::get()->getInstalled($data["composer_name"]),
                            $data['composer_type'],
                            'version'
                        );
                    },
                    'options' => [
                        'width' => '150px',
                    ]
                ],
                [
                    'label' => Yii::t('extensions-manager', 'Type'),
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
</section>