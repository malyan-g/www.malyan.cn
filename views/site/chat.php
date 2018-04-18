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
        var ws = null;
        function connectWs() {
            ws = new WebSocket('ws:service.malyan.cn');
            ws.onopen = function(e){
                heartCheck.reset().start();
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
                reconnect();
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
            
            //心跳检测
            var heartCheck = {
                timeout: 540000,        //9分钟发一次心跳
                timeoutObj: null,
                serverTimeoutObj: null,
                reset: function(){
                    clearTimeout(this.timeoutObj);
                    clearTimeout(this.serverTimeoutObj);
                    return this;
                },
                start: function(){
                    var self = this;
                    this.timeoutObj = setTimeout(function(){
                        //这里发送一个心跳，后端收到后，返回一个心跳消息，
                        //onmessage拿到返回的心跳就说明连接正常
                        ws.send("ping");
                        console.log("ping!")
                        self.serverTimeoutObj = setTimeout(function(){//如果超过一定时间还没重置，说明后端主动断开了
                            ws.close();     //如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
                        }, self.timeout)
                    }, this.timeout)
                }
            }
        }
        
        reconnect = function(){
            setTimeout(function(){
                 connectWs();
            },5000);
        };
        
        connectWs();
    });
JS;
$this->registerJs($js);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
