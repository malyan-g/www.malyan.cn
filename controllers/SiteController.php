<?php

namespace app\controllers;

use app\components\helpers\SwooleHelper;
use yii\web\Controller;
use app\components\helpers\RedisHelper;

class SiteController extends Controller
{
    public $layout = false;
    /**
     * 聊天
     * @return string
     */
    public function actionChat()
    {
        $token = md5(time());
        $redisHelper = RedisHelper::getInstance();
        $redisHelper->set($token, 1);
        $redisHelper->expire($token, 60);
        return $this->render('chat', [
            'token' => $token
        ]);
    }

    public function actionPush()
    {
        var_dump(SwooleHelper::backendPush('你哈'));
    }
}
