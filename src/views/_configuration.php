<?php
/** @var \DevGroup\ExtensionsManager\models\BaseConfigurationModel $model */
/** @var array $configurable */
/** @var \yii\bootstrap\ActiveForm $form */
?>

<?= $form->field($model, 'extensionsStorage') ?>
<?= $form->field($model, 'packagistUrl') ?>
<?= $form->field($model, 'githubAccessToken') ?>
<?= $form->field($model, 'applicationName') ?>
<?= $form->field($model, 'githubApiUrl') ?>
<?= $form->field($model, 'extensionsPerPage') ?>