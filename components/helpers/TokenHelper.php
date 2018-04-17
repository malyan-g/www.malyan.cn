<?php

/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/26
 * Time: 下午1:02
 */
namespace app\components\helpers;

use Yii;
use yii\base\Object;

/**
 * Token
 * Class TokenHelper
 * @package app\components\helpers
 */
class TokenHelper extends Object
{
    /**
     * 获取token
     * @return mixed
     */
    public static function get()
    {
        //生成一个不会重复的字符串
        $str = md5(uniqid(md5(microtime(true)), true));
        // 加密
        $token = sha1($str);
        // 存入redis
        RedisHelper::getInstance()->set($token, Yii::$app->session->id)->expire(TOKEN_EXPIRE);
        return $token;
    }
    
    /**
     *  判断token是否正确
     * @param $token
     * @return bool|mixed|string
     */
    public static function check($token)
    {
        $result = RedisHelper::getInstance()->get($token);
        if($result){
            RedisHelper::getInstance()->del($token);
        }
        return $result;
    }
}
