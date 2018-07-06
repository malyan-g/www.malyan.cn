<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/4/17
 * Time: 下午8:28
 */

use yii\helpers\Html;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $nickname string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="grayL">
<?php $this->beginBody() ?>
<div class="wrap">
    <div class="header-num" style="background: #00b3ee;text-align: center">
        <a href="#"><?= $nickname ?></a> (当前共<span class="num">1</span>人)
        <a href="http://passport.malyan.cn/logout.html" style="float: right;margin-right: 10px;">退出</a>
    </div>
    <div class="h-doc-im" id="h-doc-im">
        <div class="con"></div>
    </div>
    <div class="h-doc-chat">
        <div class="file-box">
            <input type="file" class="file" accept="image/gif,image/jpeg,image/jpg,image/png"/>
        </div>
        <div class="input-box">
            <input type="text" class="input"/>
        </div>
        <div class="send">发送</div>
    </div>
</div>
<div class="h-pic-show none">
    <img src="" alt=""/>
</div>
<?php
$js = <<<JS
    var nickname = '{$nickname}';
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
