<?php
/**
 * @var string $readme
 * @var array $versions
 * @var string $description
 * @var string $name
 * @var array $dependencies
 * @var array $authors
 * @var array $license
 * @var string $packageName
 * @var bool $installed
 * @codeCoverageIgnore
 */
use DevGroup\ExtensionsManager\ExtensionsManager;

?>
<div class="details-part">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <h3 class="panel-title pull-left">
                <?= Yii::t('extensions-manager', '{extName} detailed information', ['extName' => $name]) ?>
            </h3>
            <?php if (false === $installed) : ?>
                <?php if (Yii::$app->user->can('extensions-manager-install-extension')): ?>
                    <button class="btn btn-success btn-xs pull-right" data-action="run-ext-task"
                            data-ext-task="<?= ExtensionsManager::INSTALL_DEFERRED_TASK ?>"
                            data-package-name="<?= $packageName ?>">
                        <?= Yii::t('extensions-manager', 'Install') ?>
                    </button>
                <?php else : ?>
                    <div class="label label-warning pull-right"><?= Yii::t('extensions-manager', 'No installed') ?></div>
                <?php endif; ?>
            <?php else : ?>
                <?php if (Yii::$app->user->can('extensions-manager-uninstall-extension')): ?>
                    <button class="btn btn-danger btn-xs pull-right" data-action="run-ext-task"
                            data-ext-task="<?= ExtensionsManager::UNINSTALL_DEFERRED_TASK ?>"
                            data-package-name="<?= $packageName ?>">
                        <?= Yii::t('extensions-manager', 'Uninstall') ?>
                    </button>
                <?php else : ?>
                    <div class="label label-success pull-right"><?= Yii::t('extensions-manager', 'Installed') ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'Authors') ?></h3>
                        </div>
                        <div class="panel-body">
                            <?php foreach ($authors as $author) : ?>
                                <dl class="dl-horizontal">
                                    <dt><?= Yii::t('extensions-manager', 'Author name') ?></dt>
                                    <dd><?= $author["name"] ?></dd>
                                </dl>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'License') ?></h3>
                        </div>
                        <div class="panel-body">
                            <?php foreach ($license as $l) : ?>
                                <dl class="dl-horizontal">
                                    <dt><?= Yii::t('extensions-manager', 'License type') ?></dt>
                                    <dd><?= $l ?></dd>
                                </dl>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'Versions') ?></h3>
                        </div>
                        <div class="panel-body ext-limited">
                            <?php
                            $current = $versions['current'];
                            unset($versions['current']);
                            foreach ($versions as $name => $time) :?>
                                <dl class="dl-horizontal">
                                    <dt>
                                        <?= $name ?>
                                        <?= $name == $current ?
                                            '(' . Yii::t('extensions-manager', 'Current version') . ')'
                                            : '' ?>
                                    </dt>
                                    <dd><?= $time ?></dd>
                                </dl>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'Description') ?></h3>
                        </div>
                        <div class="panel-body">
                            <?= $description ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'Dependencies') ?></h3>
                        </div>
                        <div class="panel-body ext-limited">
                            <div class="row">
                                <?php foreach ($dependencies as $type => $deps) : ?>
                                    <div class="col-sm-6">
                                        <div>
                                            <div class="box-header">
                                                <h3 class="box-title"><?= $type ?></h3>
                                            </div>
                                            <div class="box-body">
                                                <?php foreach ($deps as $repo => $version) : ?>
                                                    <dl class="dl-horizontal">
                                                        <dt><?= $repo ?></dt>
                                                        <dd><?= $version ?></dd>
                                                    </dl>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('extensions-manager', 'Readme') ?></h3>
                        </div>
                        <div class="panel-body"><?= $readme ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
