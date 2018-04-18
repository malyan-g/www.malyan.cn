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
        // 判断当前浏览器是否支持WebSocket
        var ws = null;
        // ws服务器地址
        var wsUrl = 'ws:service.malyan.cn';
        // 避免ws重复连接
        var lockReconnect = false;  
        
         //连接ws
        createWebSocket();  
        
        function createWebSocket() {
            try{
                if('WebSocket' in window){
                    ws = new WebSocket(wsUrl);
                }else{
                    $('.message-box').append("您的浏览器不支持websocket协议,建议使用新版谷歌、火狐等浏览器，请勿使用IE10以下浏览器，360浏览器请使用极速模式，不要使用兼容模式！"); 
                }
                initEventHandle();
            }catch(e){
                reconnect(url);
                console.log(e);
            }     
        }
        
        function initEventHandle() {
            ws.onopen = function () {
                //心跳检测重置
                heartCheck.reset().start();      
                console.log("ws连接成功!"+new Date().toUTCString());
            };
            
            ws.onmessage = function (e) {
                //如果获取到消息，心跳检测重置
                heartCheck.reset().start();
                //拿到任何消息都说明当前连接是正常的
                 try{
                    var data = JSON.parse(e.data);
                    if(data.type == 'Text'){
                        $('.message-box').append('<p>' + data.msg + '</p>');
                    }
                }catch (e){}
            };
            
            ws.onclose = function () {
                reconnect();
                console.log("ws连接关闭!"+new Date().toUTCString());
            };
            
            ws.onerror = function () {
                reconnect();
                console.log("ws连接错误!");
            };
        }
        
        function send() {
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
        
        function reconnect() {
            if(lockReconnect) return;
            lockReconnect = true;
            setTimeout(function () {     
                createWebSocket();
                // 没连接上会一直重连，设置延迟避免请求过多
                lockReconnect = false;
            }, 2000);
        }
    });
JS;
$this->registerJs($js);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
