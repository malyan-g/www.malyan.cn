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
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="message-box" style="margin: 10px;line-height: 16px">
    </div>
</div>

<footer class="footer navbar-fixed-bottom" style="height: 110px">
    <div class="container">
        <div class="input-group" style="margin-bottom: 20px">
            <textarea cols="30" rows="3" class="form-control message" placeholder="请输入内容" ></textarea>
            <span class="input-group-btn">
                <button class="btn btn-info send" type="button" style="height: 74px;" onclick="send()">发送</button>
            </span>
        </div>
    </div>
</footer>
<?php
$js = <<<JS
    $(function() {
        function connectWs() {
            var ws = new WebSocket('ws:service.malyan.cn');
            ws.onopen = function(e){
            };
            
            ws.onmessage = function(e){
                 try{
                    var data = JSON.parse(e.data);
                    if(data.type == 'Text'){
                        $('.message-box').append('<p>' + data.msg + '</p>');
                    }
                }catch (e){}
            };
            
            ws.onerror = function(e){
            };
            
            ws.onclose = function(e){
            };
            
            send = function() {
                var message = $('.message').val();
                if(message != ''){
                    var data = {
                        type: 'Text',
                        msg: message
                    };
                    ws.send(JSON.stringify(data));
                    $('.message').val('');
                }
            }
        }
        connectWs();
    });
JS;
$this->registerJs($js);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
