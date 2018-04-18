var webSocket = {
    // WebSocket对象
    ws: null,
    // ws连接地址
    url: 'ws:service.malyan.cn',
    // 重连时间
    reconnectTime: 2000,
    // 心跳检测
    heartCheck: {
        // webSocket对象
        webSocketSelf: this,
        // 心跳频率
        timeout: 540000,
        // 心跳对象
        timeoutObj: null,
        // webScoket服务对象
        serverTimeoutObj: null,
        // 心跳重置
        reset: function(){
            clearTimeout(this.timeoutObj);
            clearTimeout(this.serverTimeoutObj);
            return this;
        },
        // 心跳开始
        start: function(){
            var heartSelf = this;
            //这里发送一个心跳，后端收到后，返回一个心跳消息，
            this.timeoutObj = setTimeout(function(){
                // 发送心跳消息
                var data = {
                    type: 'Ping'
                };
                this.webSocketSelf.sendMessage(data);
                this.webSocketSelf.log('Ping');
                //如果超过一定时间还没重置，说明后端主动断开了
                heartSelf.serverTimeoutObj = setTimeout(function(){
                    // 如果onclose会执行reconnect，我们执行ws.close()就行了
                    // 如果直接执行reconnect 会触发onclose导致重连两次
                    heartSelf.webSocketSelf.ws.close();
                }, heartSelf.timeout);
            }, this.timeout);
        }
    },
    // 避免ws重复连接
    lockReconnect: false,
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
    initEventHandle: function() {
        // 连接回调
        this.ws.onopen = function () {
            //心跳检测重置
            this.heartCheck.reset().start();
            this.log("ws连接成功!"+new Date().toUTCString());
        };
        // 收到消息回调
        this.ws.onmessage = function (e) {
            //如果获取到消息，心跳检测重置
            this.heartCheck.reset().start();
            //拿到任何消息都说明当前连接是正常的
            try{
                var data = JSON.parse(e.data)
                // 判断为文本消息
                if(data.type == 'Text'){
                    receiveMessage(data.msg);
                }
            }catch (e){}
            this.log(e);
        };
        // 错误回调
        this.ws.onerror = function () {
            this.reconnect();
            this.log("ws连接错误!");
        };
        // 关闭回调
        this.ws.onclose = function () {
            this.reconnect();
            this.log("ws连接关闭!"+new Date().toUTCString());
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