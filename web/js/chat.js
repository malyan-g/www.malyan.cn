socket.init();
socket.appendCon = function (con) {
    $('.h-doc-im .con').append(con);
    var dis = $('.h-doc-im .con').height() - $('.h-doc-im').height();
    this.imScroll.refresh();
    if(dis > 0) {
        dis += 30;
        this.imScroll.scrollTo(0, -dis);
    }
};
socket.onMessageText = function (data) {
    var con = '<div><a href="#" ' + (data.nickname === 'nickname' ? 'style="color:red"' : '') + '>' + data.nickname + '</a>：' + data.text + '</a></div>';
    this.appendCon(con);
};
socket.onMessageImage = function (data) {
    var con ='<div><a href="#" ' +  (data.nickname === 'nickname' ? 'style="color:red"' : '') + '>' + data.nickname + '</a>：<img src="' +data.image + '" width="100%"></div>';
    this.appendCon(con);
};
socket.onMessageConnect = function (data) {
    $('.num').html(data.num);
};
// 发送消息
$('.send').on('click', function(){
    var text = $.trim($('.h-doc-chat .input').val()).replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
    if(text != ''){
        socket.send({type: 'Text', text: text, nickname: nickname});
        $('.h-doc-chat .input').val('');
    }
});
// 上传图片
$('.h-doc-chat .file').on('change', function(){
    // 选择图片的input
    var input = this.files[0];
    var reader = new FileReader();
    try{
        reader.onload = function(e) {
            this.value = '';
            var image = e.target.result;
            if(image){
                socket.send({type: 'Image', image: image, nickname: nickname});
            }
        };
        reader.readAsDataURL(input);
    }catch(e) {
        socket.log(e);
    }
});
