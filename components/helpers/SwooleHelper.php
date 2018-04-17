<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/4/17
 * Time: 下午8:33
 */

namespace app\components\helpers;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class SwooleHelper extends Object
{
    /**
     * @var string 服务器域名
     */
    const SERVICE_DOMAIN = 'http://service.malyan.cn';

    /**
     * @var string 秘钥
     */
    const SECRET_KEY = 'malyan';

    /**
     * @var string 主机
     */
    public $host = '0.0.0.0';

    /**
     * @var int 端口号
     */
    public $port = 9801;

    /**
     * @var \swoole_websocket_server webSocket句柄
     */
    public $socket;

    /**
     * @var array 服务器配置
     */
    public $config = [
        'worker_num' => 4, // 进程数
        'daemonize' => false, // 进程是否后台运行
        'backlog' => 128, // 队列长度
        'logFile' => '/Users/M/logs/swoole-websocket-9801.log' //日志文件路径
    ];

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
        $this->socket->set($this->config);
    }

    /**
     * webSocket握手处理(只有返回true才握手成功)
     */
    private function handshake()
    {
        $this->socket->on('handshake', function(\swoole_http_request $request, \swoole_http_response $response){
            // 链接身份验证
            if($this->handshakeValidate($request)){
                $response->end();
                return false;
            }


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
