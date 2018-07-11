<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>时钟</title>
    <script src="http://static.malyan.cn/js/jquery.min.js"></script>
    <style>
        .clock-div{
            text-align: center;
            margin-top: 100px;
        }
        #clock{
        }
    </style>
</head>
<body>
<div class="clock-div">
    <canvas id="clock" width="400" height="400">你的浏览器不是兼容canvas</canvas>
</div>
<script type="text/javascript">
    $(function(){
        // 获取canvas对象和内容
        var canvas = document.getElementById('clock');
        var context = canvas.getContext('2d');
        // 获取canvas的宽和高
        var canvasWidth = canvas.width;
        var canvasHeight = canvas.height;
        // 计算宽高比例
        var rem = canvasWidth/200;
        // 设置时钟半径
        var r= canvasWidth/2;
        // 设置时钟边的宽度
        var lineWidth = 10*rem;
        // 设置时钟数字数组
        var hourNumbers = [3,4,5,6,7,8,9,10,11,12,1,2];

        //添加数字
        function drawNumber(){
            hourNumbers.forEach(function(number , i ){
                // 求弧度
                var rad = 2*Math.PI/12*i;
                var x = Math.cos(rad)*(r-30*rem);
                var y =  Math.sin(rad)*(r-30*rem);
                context.font = 18*rem + 'px Arial';
                context.textAlign = 'center';
                context.textBaseline = 'middle';
                context.fillText(number,x,y);
            });

            // 添加60个圆点
            for(var i=0;i<60;i++){
                // 求弧度
                var rad = 2*Math.PI/60*i;
                var x = Math.cos(rad)*(r-18*rem);
                var y =  Math.sin(rad)*(r-18*rem);
                context.beginPath();
                context.fillStyle = i % 5 ==0 ? '#000' : '#ccc';
                context.arc(x,y,2*rem,0,Math.PI*2,false);
                context.fill();
            }
        }
        // 创建时钟的圆形边框
        function drawBackground(){
            context.save();
            context.translate(r,r);
            context.beginPath();
            context.lineWidth =lineWidth;
            context.arc(0,0,r-lineWidth/2,0,Math.PI*2,false);
            context.stroke();
        }

        // 创建时针
        function drawHour(hour,minute){
            context.save();
            context.beginPath();
            var rad = Math.PI*2/12*hour;
            var mrad = Math.PI*2/12/60*minute;
            context.rotate(rad+mrad);
            context.lineWidth = 6*rem;
            context.lineCap = 'round';
            context.moveTo(0,10*rem);
            context.lineTo(0,-r/2);
            context.stroke();
            context.restore();
        }

        // 创建分针
        function drawMinute(minute){
            context.save();
            context.beginPath();
            var rad = Math.PI*2/60*minute;
            context.rotate(rad);
            context.lineWidth = 3*rem;
            context.lineCap = 'round';
            context.moveTo(0,10*rem);
            context.lineTo(0,-r+30*rem);
            context.stroke();
            context.restore();
        }

        // 创建秒针
        function drawSecond(second){
            context.save();
            context.beginPath();
            var rad = Math.PI*2/60*second;
            context.fillStyle = '#c14543';
            context.rotate(rad);
            context.lineCap = 'round';
            context.moveTo(-2*rem,20*rem);
            context.lineTo(2*rem,20*rem);
            context.lineTo(1,-r+18*rem);
            context.lineTo(-1,-r+18*rem);
            context.fill();
            context.restore();
        }

        // 创建中心白色圆点
        function drawDot(){
            context.beginPath();
            context.fillStyle = '#fff';
            context.arc(0,0,3,0,Math.PI*2,false);
            context.fill();
        }

        // 创建时钟动画
        function draw() {
            context.clearRect(0,0,canvasWidth,canvasHeight);
            var now = new Date();
            var hour = now.getHours();
            var minute = now.getMinutes();
            var second = now.getSeconds();
            drawBackground();
            drawNumber();
            drawDot();
            drawHour(hour,minute);
            drawMinute(minute);
            drawSecond(second);
            context.restore();
        }

        // 计时器
        setInterval(draw,1000);
    });
</script>
</body>
</html>