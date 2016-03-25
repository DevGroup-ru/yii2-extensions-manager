<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


app\assets\AppAsset::register($this);


dmstr\web\AdminLteAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <?php $this->head() ?>
</head>
<body class="skin-blue sidebar-mini">
<?php $this->beginBody() ?>
<div class="wrapper">

    <section class="content">
        <?= $content ?>
    </section>

</div>

<?php $this->endBody() ?>
<style type="text/css">
    .bootstrap-switch {
        height: 34px;
    }
</style>
</body>
</html>
<?php $this->endPage() ?>
