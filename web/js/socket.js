var socket = {
    imScroll: null,
    // webSocket对象
    ws: null,
    // webSocket服务器地址
    url: 'service.malyan.cn',
    // 开启日志
    openLog: false,
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
    // 构造方法
    init: function () {
        this.createWebSocket();
        $('.h-doc-im').css('height',$(window).height() - $('.h-doc-chat').outerHeight() - 20);
        this.imScroll = new IScroll('#h-doc-im', {click: true});
        this.iosFocus();
    },
    // 创建WebSocket对象
    createWebSocket: function () {
        try{
            if('WebSocket' in window){
                this.ws = new WebSocket('ws:' + this.url);
                this.initEventHandle();
            }else{
                this.log("您的浏览器不支持webSocket!");
            }
        }catch(e){
            this.reconnect();
            this.log(e);
        }
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
    // webSocket初始化
    initEventHandle: function () {
        var self = this;
        // 连接建立时触发
        this.ws.onopen = function (event) {
            self.log("ws open!");
            self.log(event);
            //self.send({type: 'Connect'});
            //心跳检测重置
            self.heartReset().heartStart();
        };
        // 客户端接收服务端数据时触发
        this.ws.onmessage = function(event){
            self.log("ws message!");
            //如果获取到消息，心跳检测重置
            self.heartReset().heartStart();
            //拿到任何消息都说明当前连接是正常的
            try{
                self.log(event.data);
                var data = JSON.parse(event.data);
                if(data.type === 'Text'){
                    self.onMessageText(data.data);
                }else if(data.type === 'Image'){
                    self.onMessageImage(data.data);
                }else if(data.type === 'Connect'){
                    self.onMessageConnect(data.data);
                }
            }catch (e){
                self.log(e);
                self.log(event);
            }
        };
        // 通信发生错误时触发
        this.ws.onerror = function (event) {
            self.log("ws error!");
            self.log(event);
            self.reconnect();
        };
        // 连接关闭时触发
        this.ws.onclose = function (event) {
            self.log("ws close!");
            self.log(event);
            self.reconnect();
        };
    },
    // webSocket客户端发送数据到服务器
    send: function (data) {
        try{
            this.ws.send(JSON.stringify(data))
        }catch(e){
            this.log(e)
        }
    },
    // webSocket链接后Ping服务器
    ping: function () {
        this.send({type: 'Ping'});
        this.log('ping');
    },
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
            self.ping();
            //如果超过一定时间还没重置，说明后端主动断开了
            self.heartServerTimeoutObj = setTimeout(function(){
                // 如果onclose会执行reconnect，我们执行ws.close()就行了
                // 如果直接执行reconnect 会触发onclose导致重连两次
                self.ws.close();
            }, self.heartTimeout);
        }, this.heartTimeout);
    },
    iosFocus:function(){
        var u = navigator.userAgent, app = navigator.appVersion;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端

        if (isiOS) {
            $('.h-doc-chat .input').focus(function () {
                //var self = thisl
                setTimeout(function(){
                    window.scrollTo(0, $('body').height());
                    //self.scrollIntoView(true);
                }, 500);
            });
        }
    },
    // 日志
    log: function (message) {
        if(this.openLog) console.log(message);
    }
};
