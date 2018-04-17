<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/27
 * Time: 上午11:38
 */

namespace app\components\helpers;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class SignHelper
 * @package app\components\helpers
 */
class SignHelper extends Object
{
    /**
     * 加密
     * @param $data
     * @param $key
     * @return string
     */
    public static function encrypt($data, $key)
    {
        if(!$data ||  !$key){
            return '';
        }
        ksort($data);
        reset($data);
        $signStr = "";
        foreach($data as $k => $v){
            if($k != 'sign' && $k != 'r' ){
                $signStr .= "&" . $k . "=" . trim($v);
            }
        }
        $sign =  md5(trim($signStr, "&") . $key);
        return $sign;
    }
}
