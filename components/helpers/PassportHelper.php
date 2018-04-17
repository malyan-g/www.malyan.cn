<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/3/30
 * Time: 上午11:20
 */

namespace app\components\helpers;


use yii\base\Object;
use yii\helpers\Url;

class PassportHelper extends Object
{
    private static $domain = 'http://passport.malyan.cn';

    private static $loginRoute = '/login.html?redirect_url=';

    private static $logoutRoute = '/logout.html?redirect_url=';


    public static function loginRoute()
    {
        return self::$domain . self::$loginRoute . Url::home(true);
    }

    public static function logoutRoute()
    {
        return self::$domain . self::$logoutRoute . Url::home(true);
    }
}