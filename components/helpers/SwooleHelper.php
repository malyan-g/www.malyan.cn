<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/4/17
 * Time: 下午8:33
 */

namespace app\components\helpers;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class SwooleHelper extends BaseObject
{
    /**
     * 服务器域名
     * @var string
     */
    const SERVICE_DOMAIN = 'http://service.malyan.cn';

    /**
     * 秘钥
     * @var string
     */
    const SECRET_KEY = 'malyan';

    /**
     * 主机
     * @var string
     */
    public $host = '0.0.0.0';

    /**
     * 端口号
     * @var int
     */
    public $port = 9801;

    /**
     * webSocket对象
     * @var \swoole_websocket_server
     */
    public $socket;

    /**
     * 线程数（一般设置为CPU核数的1-4倍，默认会启用CPU核数相同的数量）
     * 线程数必须小于或等于worker进程数
     * @var int
     */
    public $reactorNum = 2;

    /**
     *  worker进程数，业务代码是全异步非阻塞的，这里设置为CPU的1-4倍最合理
     * @var int
     */
    public $workerNum = 4;

    /**
     * worker进程的最大任务数，默认为0
     * @var int
     */
    public $maxRequest = 0;

    /**
     * 最大允许的连接数，默认值为ulimit -n的值
     * 最大不得超过操作系统ulimit -n的值，否则会报一条警告信息，并重置为ulimit -n的值
     * @var
     */
    public $maxConn = 0;

    /**
     * 配置Task进程的数量，配置此参数后将会启用task功能
     * 所以Server务必要注册onTask、onFinish2个事件回调函数。如果没有注册，服务器程序将无法启动
     * @var int
     */
    public $taskWorkerNum = 0;

    /**
     * 设置task进程与worker进程之间通信的方式。
     * 1, 使用unix socket通信，默认模式
     * 2, 使用消息队列通信
     * 3, 使用消息队列通信，并设置为争抢模式
     * @var int
     */
    public $taskIpcMode = 0;

    /**
     * 设置task进程的最大任务数，默认值0
     * @var int
     */
    public $taskMaxRequest = 0;

    /**
     * 设置task的数据临时目录，默认会使用/tmp目录存储task数据
     * @var
     */
    public $taskTmpDir = '/data/swoole';

    /**
     * 数据包分发策略。可以选择5种类型，默认为2
     * 1，轮循模式，收到会轮循分配给每一个worker进程
     * 2，固定模式，根据连接的文件描述符分配worker。这样可以保证同一个连接发来的数据只会被同一个worker处理
     * 3，抢占模式，主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker
     * 4，IP分配，根据客户端IP进行取模hash，分配给一个固定的worker进程。可以保证同一个来源IP的连接数据总会被分配到同一个worker进程。算法为 ip2long(ClientIP) % worker_num
     * 5，UID分配，需要用户代码中调用 $serv-> bind() 将一个连接绑定1个uid。然后swoole根据UID的值分配到不同的worker进程。算法为 UID % worker_num，如果需要使用字符串作为UID，可以使用crc32(UID_STRING)
     * @var int
     */
    public $dispatchMode = 2;

    /**
     * 设置消息队列的KEY，仅在task_ipc_mode = 2/3时使用。
     * 设置的Key仅作为Task任务队列的KEY，此参数的默认值为ftok($php_script_file, 1)
     * @var string
     */
    public $messageQueueKey;

    /**
     * 守护进程化。设置daemonize => 1时，程序将转入后台作为守护进程运行。
     * 长时间运行的服务器端程序必须启用此项。
     * @var int
     */
    public $daeMonIze = 1;

    /**
     * Listen队列长度，如backlog => 128，此参数将决定最多同时有多少个等待accept的连接
     * @var int
     */
    public $backlog = 128;

    /**
     * swoole错误日志文件，默认会打印到屏幕。
     * @var string
     */
    public $logFile = '/data/logs/swoole-websocket-9801.log';

    /**
     * 错误日志打印的等级，范围是0-5，默认是0 也就是所有级别都打印
     * 0 =>DEBUG    1 =>TRACE     2 =>INFO     3 =>NOTICE       4 =>WARNING       5 =>ERROR
     * @var int
     */
    public $logLevel = 0;

    /**
     * 启用心跳检测，此选项表示每隔多久轮循一次，单位为秒。
     * 如 heartbeat_check_interval => 60，表示每60秒，遍历所有连接
     * 如果该连接在60秒内，没有向服务器发送任何数据，此连接将被强制关闭。
     * @var int
     */
    public $heartbeatCheckInterval = 60;

    /**
     * 与heartbeat_check_interval配合使用。表示连接最大允许空闲的时间
     * 表示每60秒遍历一次，一个连接如果600秒内未向服务器发送任何数据，此连接将被强制关闭
     * @var int
     */
    public $heartbeatIdleTime = 600;

    /**
     * 初始化
     */
    public function init()
    {
        $this->socket = new \swoole_websocket_server($this->host, $this->port);

        $this->set();

        $this->handshake();

        $this->open();

        $this->message();

        $this->close();

        $this->request();

        $this->socket->start();
    }

    /**
     * 设置服务器配置
     */
    private function set()
    {
        $config = [
            'worker_num' => 4,
            'backlog' => 128,
            'daemonize' => 1,
            'dispatch_mode' => 2,
        ];
        $this->socket->set($config);
    }

    /**
     * webSocket握手处理(只有返回true才握手成功)
     */
    private function handshake()
    {
        $this->socket->on('handshake', function(\swoole_http_request $request, \swoole_http_response $response){
            // 链接身份验证
            /*if($this->handshakeValidate($request)){
                $response->end();
                return false;
            }*/


            //自定定握手规则，没有设置则用系统内置的（只支持version:13的）
            if(!isset($request->header['sec-websocket-key'])) {
                $response->end();
                return false;
            }

            // webSocket握手连接算法验证
            $secWebSocketKey = $request->header['sec-websocket-key'];
            $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
            if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
                $response->end();
                return false;
            }

            $key = base64_encode(sha1(
                $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            ));

            $headers = [
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Accept' => $key,
                'Sec-WebSocket-Version' => '13',
            ];

            if (isset($request->header['sec-websocket-protocol'])) {
                $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
            }

            foreach ($headers as $key => $val) {
                $response->header($key, $val);
            }

            $response->status(101);
            $response->end();
            return true;
        });
    }

    /**
     * 身份验证
     * @param \swoole_http_request $request
     * @return bool
     */
    private function handshakeValidate(\swoole_http_request $request)
    {
        $token = ArrayHelper::getValue($request->get, 'token');

        if($token){
            $redisHelper = RedisHelper::getInstance();
            if($redisHelper->exists($token)){
                $redisHelper->del($token);
                return false;
            }
        }

        return true;
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
     */
    private function open()
    {
        $this->socket->on('open', function (\swoole_websocket_server $server, \swoole_http_request $request) {

        });
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     */
    private function message()
    {
        $this->socket->on('message', function (\swoole_websocket_server $server,\swoole_websocket_frame $frame) {
            try{
                $data = json_decode($frame->data);
                if(isset($data->type)){
                    $method = 'onMessage' . $data->type;
                    if(method_exists($this, $method)){
                        $this->$method($frame->fd, $data);
                    }
                }
            }catch (\Exception $e){
                Yii::error('swoole-websocket-data:' . $frame->data);
            }
        });
    }

    /**
     * 当WebSocket客户端与服务器断开连接并完成握手后会回调此函数
     */
    private function close()
    {
        $this->socket->on('close', function (\swoole_websocket_server $server, $fd) {

        });
    }

    /**
     * 推送消息
     * @param $data
     * @param null $fd
     */
    private function push($data, $fd = null)
    {
        $data = json_encode($data);
        if($fd){
            $this->socket->push($fd, $data);
        }else{
            foreach($this->socket->connections as $fd){
                $connectionInfo = $this->socket->connection_info($fd);
                if($connectionInfo['websocket_status']){
                    $this->socket->push($fd, $data);
                }
            }
            unset($connectionInfo);
        }
    }

    /**
     * 发送文本消息
     * @param $fd
     * @param $data
     */
    private function onMessageText($fd, $data)
    {
        $data = [
            'type' => 'Text',
            'msg' => $data->msg
        ];
        $this->push($data);
    }

    /**
     * 请求服务器会回调此函数
     */
    private function request()
    {
        $this->socket->on('Request', function (\swoole_http_request $request, \swoole_http_response $response) {
            $responseData = [
                'code' => 50000,
                'msg' => '请求失败'
            ];

            if($this->requestValidate($request->post)){
                //判断下是否有数据
                if(isset($request->post['content'])){

                    $data = [
                        'type' => 'Text',
                        'msg' => htmlspecialchars($request->post['content'])
                    ];
                    $this->push($data);

                    $responseData = [
                        'code' => 10000,
                        'msg' => '推送成功'
                    ];
                }
            }

            $response->end(json_encode($responseData));
        });
    }

    /**
     * 请求验证
     * @param $data
     * @return bool
     */
    private function requestValidate($data)
    {
        if(!is_array($data) || !isset($data['sign'])){
            return false;
        }

        $sign = ArrayHelper::getValue($data, 'sign');
        if(!$sign || $sign !== SignHelper::encrypt($data, self::SECRET_KEY)){
            return false;
        }

        return true;
    }

    /**
     * 消息推送
     * @param $content
     * @return bool
     */
    public static function backendPush($content)
    {
        try{
        $result =  HttpClientHelper::getInstance()->setUrl(self::SERVICE_DOMAIN)->setMethod('post')->setData([
            'content' => $content,
            'sign' => SignHelper::encrypt(['content' => $content], self::SECRET_KEY)
        ])->send();

        $data = json_decode($result);
        return $data->code === 10000;
        }catch (\Exception $e){
            return false;
        }
    }
}
