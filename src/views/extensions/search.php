<?php
/**
 * @var \yii\web\View $this
 * @var string $type
 */
use yii\helpers\Html;
use kartik\icons\Icon;
use yii\grid\GridView;
use DevGroup\ExtensionsManager\ExtensionsManager;
use DevGroup\ExtensionsManager\models\Extension;


\DevGroup\ExtensionsManager\assets\AdminBundle::register($this);
\DevGroup\DeferredTasks\assets\AdminBundle::register($this);

$sortBy = [];
$gridTpl = <<<TPL
<div class="box-body">
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
$this->title = Yii::t('extensions-manager', 'Extensions search');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-3">
        <div class="box box-solid configuration-navigation">
            <div class="box-header">
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
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="extensions-controller__search-extensions box box-solid">
            <div class="box-header clearfix">
                <h3 class="box-title pull-left">
                    <?= Yii::t('extensions-manager', 'Extensions search') ?>
                </h3>
                <div class="pull-right">
                    <?= Html::beginForm(['/extensions-manager/extensions/search'], 'GET', ['class' => 'form-inline']) ?>
                    <div class="form-group">
                        <?= Html::dropDownList('sort', Yii::$app->request->get('sort'), $sortBy,
                            [
                                'class' => 'form-control',
                                'prompt' => Yii::t('extensions-manager', 'Sort by')
                            ]) ?>
                        <?= Html::dropDownList('type', $type, Extension::getTypes(),
                            [
                                'class' => 'form-control',
                                'prompt' => Yii::t('extensions-manager', 'Extension type')
                            ]) ?>
                    </div>
                    <div class="input-group" style="width: 150px;">
                        <input type="text" name="query" value="<?= Yii::$app->request->get('query') ?>"
                               class="form-control pull-right"
                               placeholder="Search">

                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
            <?= GridView::widget([
                'id' => 'ext-search-list',
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
                        'label' => Yii::t('extensions-manager', 'Package name'),
                        'content' => function ($data) {
                            return $data->getName();
                        },
                    ],
                    [
                        'label' => Yii::t('extensions-manager', 'Details'),
                        'content' => function ($data) {
                            return Html::button(Yii::t('extensions-manager', 'Details') .
                                '  ' . Icon::show('refresh fa-spin', ['style' => 'display: none;']),
                                [
                                    'class' => 'btn btn-info btn-xs',
                                    'data-package-name' => $data->getName(),
                                    'data-action' => 'ext-info'
                                ]);
                        },
                        'options' => [
                            'width' => '200px',
                        ]
                    ],
                    [
                        'label' => Yii::t('extensions-manager', 'Downloads'),
                        'content' => function ($data) {
                            return Html::tag('span',
                                Icon::show('arrow-down') . ' ' . $data->getDownloads()
                            );
                        },
                    ],
                    [
                        'label' => Yii::t('extensions-manager', 'Favers'),
                        'content' => function ($data) {
                            return Html::tag('span',
                                Icon::show('star') . ' ' . $data->getFavers()
                            );
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>