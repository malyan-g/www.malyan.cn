var webSocket = {
    // WebSocket对象
    ws: null,
    // ws连接地址
    url: 'ws:service.malyan.cn',
    // 避免ws重复连接
    lockReconnect: false,
    // 重连时间
    reconnectTime: 2000,
    // 心跳频率
    heartTimeout: 300000,
    // 心跳对象
    heartTimeoutObj: null,
    // webScoket服务对象
    heartServerTimeoutObj: null,
    // 心跳重置
    heartReset: function(){
        clearTimeout(this.heartTimeoutObj);
        clearTimeout(this.heartServerTimeoutObj);
        return this;
    },
    // 心跳开始
    heartStart: function(){
        var self = this;
        //这里发送一个心跳，后端收到后，返回一个心跳消息，
        this.heartTimeoutObj = setTimeout(function(){
            // 发送心跳消息
            var data = {
                type: 'Ping'
            };
            self.ws.send(JSON.stringify(data));
            self.log('Ping');
            //如果超过一定时间还没重置，说明后端主动断开了
            self.heartServerTimeoutObj = setTimeout(function(){
                // 如果onclose会执行reconnect，我们执行ws.close()就行了
                // 如果直接执行reconnect 会触发onclose导致重连两次
                self.ws.close();
            }, self.heartTimeout);
        }, this.heartTimeout);
    },
    // 初始化化
    init: function () {
        this.createWebSocket();
    },
    // 创建WebSocket对象
    createWebSocket: function () {
        try{
            if('WebSocket' in window){
                this.ws = new WebSocket(this.url);
            }else{
                $('.message-box').append("您的浏览器不支持websocket协议,建议使用新版谷歌、火狐等浏览器，请勿使用IE10以下浏览器，360浏览器请使用极速模式，不要使用兼容模式！");
            }
            this.initEventHandle();
        }catch(e){
            this.reconnect();
            this.log(e);
        }
    },
    // 初始化webScoket句柄
    initEventHandle: function(){
        var self = this;
        // 连接回调
        this.ws.onopen = function () {
            //心跳检测重置
            self.heartReset().heartStart();
            self.log("ws连接成功!"+new Date().toUTCString());
        };
        // 收到消息回调
        this.ws.onmessage = function (e) {
            //如果获取到消息，心跳检测重置
            self.heartReset().heartStart();
            //拿到任何消息都说明当前连接是正常的
            try{
                var data = JSON.parse(e.data)
                // 判断为文本消息
                if(data.type == 'Text'){
                    receiveMessage(data.msg);
                }
            }catch (e){}
            self.log(e.data);
        };
        // 错误回调
        this.ws.onerror = function () {
            this.reconnect();
            this.log("ws连接错误!");
        };
        // 关闭回调
        this.ws.onclose = function () {
            self.reconnect();
            self.log("ws连接关闭!"+new Date().toUTCString());
        };
    },
    // 重新连接
    reconnect:  function() {
        var self = this;
        if(this.lockReconnect) return;
        // 锁定重新连接
        this.lockReconnect = true;
        // 每2秒重连一次
        setTimeout(function () {
            self.createWebSocket();
            // 没连接上会一直重连，设置延迟避免请求过多
            self.lockReconnect = false;
        }, this.reconnectTime);
    },
    // 发送消息
    sendMessage: function(message) {
        var data = {
            type: 'Text',
            msg: message
        };
        this.ws.send(JSON.stringify(data));
    },
    // 日志
    log: function (message) {
        console.log(message);
    }
};

// 初始化
webSocket.init();

// 接受消息
receiveMessage = function(message) {
    $('.message-box').append('<p>' + message + '</p>');
};

// 发送消息
sendMessage = function() {
    var message = $('.message').val();
    if(message != ''){
        webSocket.sendMessage(message);
        $('.message').val('');
    }
};

$(document).keyup(function(e) {
    if (e.keyCode == 13) {
        sendMessage();
    }
});
