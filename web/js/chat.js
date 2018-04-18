socket.init();
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
