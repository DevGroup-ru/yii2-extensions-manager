<?php
/**
 * @var \yii\web\View $this
 * @var string $type
 */
use yii\helpers\Url;
use yii\helpers\Html;
use DevGroup\ExtensionsManager\models\Extension;
use kartik\icons\Icon;
use yii\grid\GridView;

\DevGroup\ExtensionsManager\assets\AdminBundle::register($this);
\DevGroup\DeferredTasks\assets\AdminBundle::register($this);

$sortBy = [];
$detailsUrl = Url::to(['/extensions-manager/extensions/details']);
$installUrl = Url::to(['/extensions-manager/extensions/install']);
$uninstallUrl = Url::to(['/extensions-manager/extensions/uninstall']);
$endpointUrl = Url::to(['/extensions-manager/extensions/deferred-report-queue-item']);
$JS = <<<JS
    window.ExtensionsManager = window.ExtensionsManager || {};
    window.ExtensionsManager.detailsUrl = '$detailsUrl';
    window.ExtensionsManager.installUrl = '$installUrl';
    window.ExtensionsManager.uninstallUrl = '$uninstallUrl';
    window.ExtensionsManager.endpointUrl = '$endpointUrl';
    window.ExtensionsManager.detailsTemplate = '<tr class="extension-info-tr"><td colspan="4">{details}</td></tr>';
JS;
$this->registerJs($JS, \yii\web\View::POS_HEAD);
?>
<section>
    <div class="manage-controller__search-extensions box">
        <div class="box-header">
            <h3 class="box-title"><?= Yii::t('extensions-manager', 'Extensions search') ?></h3>

            <div class="box-tools">
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
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => [
                'class' => 'table table-bordered table-hover dataTable',
            ],
            'columns' => [
                [
                    'label' => Yii::t('extensions-manager', 'Packet name'),
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
</section>