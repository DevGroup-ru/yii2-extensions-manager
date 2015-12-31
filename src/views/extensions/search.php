<?php
use yii\helpers\Html;
use DevGroup\ExtensionsManager\models\Extension;
use kartik\icons\Icon;
use yii\grid\GridView;
\DevGroup\ExtensionsManager\assets\AdminBundle::register($this);
$sortBy = [];
?>
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
                <?= Html::dropDownList('type', Yii::$app->request->get('type'), Extension::getTypes(),
                    [
                        'class' => 'form-control',
                        'prompt' => Yii::t('extensions-manager', 'Extension type')
                    ]) ?>
            </div>
            <div class="input-group" style="width: 150px;">
                <input type="text" name="query" value="<?= Yii::$app->request->get('query') ?>" class="form-control pull-right"
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
                    return Html::button(Yii::t('extensions-manager', 'Details'), [
                        'class' => 'btn btn-info btn-xs',
                        'data-repo' => $data->getName(),
                        'data-action' => 'ext-info'
                    ]);
                },
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
    <!--    <div id="ext-search-list" class="grid-view">-->
    <!--        <div class="box-body">-->
    <!--            <div class="row">-->
    <!--                <div class="col-sm-12">-->
    <!--                    <table class="table table-bordered table-hover dataTable">-->
    <!--                        <thead>-->
    <!--                        <tr>-->
    <!--                            <th>Name</th>-->
    <!--                            <th>Details</th>-->
    <!--                            <th>Downloads</th>-->
    <!--                            <th>Favers</th>-->
    <!--                        </tr>-->
    <!--                        </thead>-->
    <!--                        <tbody>-->
    <!--                        <tr data-key="0">-->
    <!--                            <td>devgroup/yii2-jsoneditor</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info loaded"-->
    <!--                                        data-repo="devgroup/yii2-jsoneditor" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  7558</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  6</span></td>-->
    <!--                        </tr>-->
    <!---->
    <!--                        <tr data-key="1">-->
    <!--                            <td>devgroup/yii2-dropzone</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info" data-repo="devgroup/yii2-dropzone"-->
    <!--                                        data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  3869</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  9</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="2">-->
    <!--                            <td>devgroup/yii2-ace-widget</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-ace-widget" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  3142</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  5</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="3">-->
    <!--                            <td>devgroup/yii2-jstree-widget</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-jstree-widget" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  1995</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  2</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="4">-->
    <!--                            <td>devgroup/yii2-multilingual</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-multilingual" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  223</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  2</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="5">-->
    <!--                            <td>devgroup/yii2-admin-utils</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-admin-utils" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  73</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  0</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="6">-->
    <!--                            <td>devgroup/yii2-extensions-manager</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-extensions-manager" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  12</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  0</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="7">-->
    <!--                            <td>devgroup/yii2-deferred-tasks</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-deferred-tasks" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  45</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  6</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="8">-->
    <!--                            <td>devgroup/yii2-polyglot</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info" data-repo="devgroup/yii2-polyglot"-->
    <!--                                        data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  26</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  1</span></td>-->
    <!--                        </tr>-->
    <!--                        <tr data-key="9">-->
    <!--                            <td>devgroup/yii2-frontend-utils</td>-->
    <!--                            <td>-->
    <!--                                <button type="button" class="btn btn-xs btn-info"-->
    <!--                                        data-repo="devgroup/yii2-frontend-utils" data-action="ext-info">Details-->
    <!--                                </button>-->
    <!--                            </td>-->
    <!--                            <td><span><i class="fa fa-arrow-down"></i>  24</span></td>-->
    <!--                            <td><span><i class="fa fa-star"></i>  0</span></td>-->
    <!--                        </tr>-->
    <!--                        </tbody>-->
    <!--                    </table>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="row">-->
    <!--                <div class="col-sm-5">-->
    <!--                    <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">-->
    <!--                        <div class="summary">Показаны записи <b>1-10</b> из <b>18</b>.</div>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--                <div class="col-sm-7">-->
    <!--                    <ul class="pagination">-->
    <!--                        <li class="prev disabled"><span>«</span></li>-->
    <!--                        <li class="active"><a-->
    <!--                                href="/ru/extensions-manager/extensions/search?sort=&amp;type=yii2-extension&amp;query=devgroup&amp;page=1&amp;per-page=10"-->
    <!--                                data-page="0">1</a></li>-->
    <!--                        <li>-->
    <!--                            <a href="/ru/extensions-manager/extensions/search?sort=&amp;type=yii2-extension&amp;query=devgroup&amp;page=2&amp;per-page=10"-->
    <!--                               data-page="1">2</a></li>-->
    <!--                        <li class="next"><a-->
    <!--                                href="/ru/extensions-manager/extensions/search?sort=&amp;type=yii2-extension&amp;query=devgroup&amp;page=2&amp;per-page=10"-->
    <!--                                data-page="1">»</a></li>-->
    <!--                    </ul>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
</div>
</section>