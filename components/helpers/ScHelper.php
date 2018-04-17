<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/27
 * Time: 下午5:40
 */

namespace app\components\helpers;

use yii\base\Object;

/**
 * Class ScHelper
 * @package app\components\helpers
 */
class ScHelper extends Object
{
    //密钥为24位16进制 向量为8
    protected static $desKey= '4d89g13j4j91j27c582ji69373y788r9';
    protected static $desIv = 'f5e68737ead431bb';

    /**
     * 将Hex转化成字符串
     * @param $hex
     * @return string
     */
    protected static function hexToString($hex)
    {
        $temp = '';
        for($i=2; $i<strlen($hex)+2; $i+=2) {
            $temp .= chr(hexdec(substr($hex, $i-2, 2)));
        }
        return $temp;
    }

    /**
     * 调用MD5加密
     * @param $val
     * @return mixed
     */
    protected static function binMd5($val)
    {
        return pack("H32", md5($val));
    }

    /**
     * DES CBC  加密
     * @param $data
     * @param $theKey
     * @param string $theIv
     * @param string $encodeType
     * @param string $mod
     * @return bool
     */
    public static function desCbcEncode($data, $theKey, $theIv = "", $encodeType = MCRYPT_TRIPLEDES, $mod = MCRYPT_MODE_CBC)
    {
        ////////////////////////////////////3des ecb/pkcs5 加密/////////////////
        $td = mcrypt_module_open($encodeType, '', $mod, '');
        if($td == false) {
            return false;
        }

        if($theIv == "") {
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND );
        } else {
            $iv = $theIv;
        }

        $ks = mcrypt_enc_get_key_size($td);
        $key = substr($theKey, 0, $ks);

        if(mcrypt_generic_init($td, $key, $iv) < 0) {
            mcrypt_module_close($td);
            return false;
        }

        //pkcs5填充
        $blockSize = mcrypt_module_get_algo_block_size ($encodeType);
        $tmpLen = $blockSize - strlen($data)% $blockSize;
        for($i = 0; $i < $tmpLen; $i++) {
            $data .= pack("h", $tmpLen);
        }

        $encodeStr = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $encodeStr;
    }

    /**
     * DES CBC 解密
     * @param $data
     * @param $theKey
     * @param string $theIv
     * @param string $encodeType
     * @param string $mod
     * @return bool
     */
    protected static function desCbcDecode($data, $theKey, $theIv = "", $encodeType = MCRYPT_TRIPLEDES, $mod = MCRYPT_MODE_CBC)
    {
        ////////////////////////////////////3des ecb/pkcs5 加密/////////////////
        $td = mcrypt_module_open($encodeType, '', $mod, '');
        if($td == false) {
            return false;
        }

        if($theIv == "") {
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $theIv;
        }

        $ks = mcrypt_enc_get_key_size($td);
        $key = substr($theKey, 0, $ks);

        if(mcrypt_generic_init($td, $key, $iv) < 0) {
            mcrypt_module_close($td);
            return false;
        }
        $decodeStr = mdecrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);


        //去掉pkcs5填充
        $strLen = strlen($decodeStr);
        $theLastPosChar =  substr($decodeStr, -1);
        $theFill = hexdec(bin2hex($theLastPosChar));
        $decodeStr = substr($decodeStr, 0, $strLen - $theFill);

        return $decodeStr;
    }

    /**
     * Des加密
     * @param $input
     * @param $des_key
     * @param $des_iv
     * @return bool
     */
    public static function desEncode( $input, $des_key, $des_iv )
    {
        $des_key = static::hexToString($des_key);
        $des_iv  = static::hexToString($des_iv);
        $TokenValue  = static::desCbcEncode($input,$des_key, $des_iv);
        $TokenValue = base64_encode($TokenValue);
        return $TokenValue;
    }

    /**
     * Des解密
     * @param $input
     * @param $des_key
     * @param $des_iv
     * @return bool
     */
    public static function desDecode( $input, $des_key, $des_iv )
    {
        $des_key = static::hexToString($des_key);
        $des_iv  = static::hexToString($des_iv);
        $responseToken = trim($input);
        $responseToken = base64_decode($responseToken);
        $responseValue = static::desCbcDecode($responseToken, $des_key, $des_iv);

        return $responseValue;
    }

    /**
     * 用户信息加密
     * @param $userArray
     * @return null
     */
    public static function encode($data)
    {
        if(is_array($data)){
            return urlencode(self::desEncode(json_encode($data), self::$desKey, self::$desIv));
        }
        return null;
    }

    /**
     * 用户信息解密
     * @param $str
     * @return null
     */
    public static function decode( $str )
    {
        if($str){
            return json_decode(self::desDecode(urldecode($str), self::$desKey, self::$desIv), TRUE);
        }
        return null;
    }
}
