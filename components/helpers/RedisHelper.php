<?php

/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/26
 * Time: 下午1:06
 */
namespace app\components\helpers;

use Yii;
use yii\base\BaseObject;

/**
 * Redis
 * Class RedisHelper
 * @package app\components\helpers
 */
class RedisHelper extends BaseObject
{
    /**
     * \Redis
     * @var $_Instance
     */
    private static $_Instance = null;

    /**
     * 实例化redis
     */
    public function init()
    {
        self::$_Instance = Yii::$app->redis;
    }

    /**
     * 获取实例
     * @return \Redis
     */
    public static function getInstance()
    {
        if(!self::$_Instance instanceof \Redis){
            new self;
        }
        return self::$_Instance;
    }
}
