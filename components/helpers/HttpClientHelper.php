<?php

/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/4/10
 * Time: 下午6:32
 */
namespace app\components\helpers;

/**
 * HttpClient助手
 * Class HttpClientHelper
 * @package app\helpers
 */
class HttpClientHelper
{
    /**
     * curl handler
     * @var resource
     */
    private $_ch;

    /**
     * 请求地址
     * @var string
     */
    private $_url;

    /**
     * 请求完全地址
     * @var string.
     */
    private $_fullUrl;

    /**
     * 请求方式
     * @var string
     */
    private $_method = 'GET';

    /**
     * 请求cookie
     * @var array
     */
    private $_cookies = [];

    /**
     * 请求header
     * @var array
     */
    private $_headers = [];

    /**
     * 是否用https请求
     * @var bool
     */
    private $_ssl = false;

    private $_format = 'JSON';

    /**
     * 请求内容
     * @var array
     */
    private $_data = null;

    /**
     * 请求失败重试次数（包括第一次请求）
     * @var int
     */
    private $_times = 1 ;

    /**
     * 状态码
     * @var int
     */
    private $_code = 0;

    /**
     * 错误信息
     * @var  string
     */
    private $_msg;

    /**
     * HttpClientHelper
     * @var $_Instance
     */
    private static $_Instance = null;

    /**
     * 初始化curl
     */
    private function __construct()
    {
        $this->_ch = curl_init();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 获取HttpClientHelper实例
     * @return HttpClientHelper|null
     */
    public static function getInstance()
    {
        if(!self::$_Instance instanceof HttpClientHelper){
            self::$_Instance = new self;
        }

        return self::$_Instance;
    }

    /**
     * 设置请求地址
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        if($url){
            $this->_url = $url;
        }
        return $this;
    }

    /**
     * 设置请求方式
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        if($method){
            $this->_method = $method;
        }
        return $this;
    }

    /**
     * 设置请求header
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        if($headers){
            $this->_headers = $headers;
        }
        return $this;
    }

    /**
     * 设置请求cookie
     * @param $cookies
     * @return $this
     */
    public function setCookies($cookies)
    {
        if($cookies){
            $this->_cookies = $cookies;
        }
        return $this;
    }

    /**
     * 设置https请求
     * @param bool $ssl
     * @return $this
     */
    public function setSsl($ssl = true)
    {
        $this->_ssl = $ssl;
        return $this;
    }

    /**
     * 设置请求数据
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * 设置请求次数（最多3次）
     * @param int $times
     * @return $this
     */
    public function setTimes($times = 1)
    {
        if($times > 1){
            $this->_times = min(3, $times);
        }
        return $this;
    }

    /**
     * 获得完整的url
     */
    private function createFullUrl()
    {
        $this->_fullUrl = $this->_url;
        if (strtoupper($this->_method) == 'GET' && $this->_data) {
            if (strpos($this->_fullUrl, '?') === false) {
                $this->_fullUrl .= '?';
            } else {
                $this->_fullUrl .= '&';
            }
            $this->_fullUrl .= http_build_query($this->_data);
        }
    }

    /**
     * 发送请求
     * @return array|bool|mixed
     */
    public function send()
    {
        $result = false;
        if(!$this->_url){
            return $result;
        }
        $this->createFullUrl();

        // 设置url
        curl_setopt($this->_ch, CURLOPT_URL, $this->_fullUrl);

        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_HEADER, false);
        curl_setopt($this->_ch, CURLOPT_AUTOREFERER, true);
        // 设置发起链接的等待秒数
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 设置允许执行的最长秒数
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");

        // 设置https请求
        if($this->_ssl){
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        // 设置请求方式
        $method = strtoupper($this->_method);
        if($method == 'GET'){
            curl_setopt($this->_ch, CURLOPT_HTTPGET, true);
        }else if($method == 'POST') {
            curl_setopt($this->_ch, CURLOPT_POST, true);
            // 设置请求数据
            if($this->_data){
                curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->_data);
            };
        }else {
            curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
            if($this->_data){
                $data = http_build_query($this->_data);
                $this->_headers['Content-Length'] = strlen($data);
                curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        // 设置header
        if($this->_headers){
            foreach($this->_headers as $key => $val){
                $headers[] = "$key:$val";
            }
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 设置cookie
        if($this->_cookies){
            foreach($this->_cookies as $key => $val)
            {
                $cookie[] = "$key=$val";
            }
            curl_setopt($this->_ch, CURLOPT_COOKIE, implode('; ', $cookie));
        }

        // 请求次数
        while($this->_times > 0){
            $result = curl_exec($this->_ch);
            if($this->_code = curl_errno($this->_ch)){
                $this->_msg = curl_error($this->_ch);
            }else{
                $this->_code = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
            }
            $this->_times--;
            if($this->_code == 200){
                break;
            }
        }
        if($this->_code != 200){
            return ['code' => $this->_code, 'msg' => $this->_msg];
        }
        return $result;
    }
}
