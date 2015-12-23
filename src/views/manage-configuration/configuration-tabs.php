<?php
/** @var array $currentConfigurable */
/** @var \DevGroup\ExtensionsManager\models\BaseConfigurationModel $currentConfigurationModel */
/** @var string $currentConfigurationView */
/** @var array $configurables */
/** @var integer $sectionIndex */
use yii\bootstrap\Nav;
$navItems = [];
foreach ($configurables as $index => $item) {
    $navItem = [
        'label' => $item['sectionNameTranslated'],
        'url' => ['configuration-index', 'sectionIndex' => $index],
    ];
    if ($index === $sectionIndex) {
        $navItem['active'] = true;
    }
    $navItems[] = $navItem;

}
?>
<div class="row">
    <div class="col-md-3">
        <div class="box box-solid configuration-navigation">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-cogs"></i>
                    <?= Yii::t('extensions-manager', 'Configuration') ?>
                </h3>
            </div>
            <div class="box-body no-padding">
                <?=
                Nav::widget([
                    'items' => $navItems,
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                ])
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $currentConfigurable['sectionNameTranslated'] ?>
                </h3>
            </div>
            <div class="box-body configuration-workarea">
                <?=
                $this->render(
                    $currentConfigurationView,
                    [
                        'model' => $currentConfigurationModel,
                        'configurable' => $currentConfigurable,
                    ]
                )
                ?>
            </div>
        </div>
    </div>
</div>

