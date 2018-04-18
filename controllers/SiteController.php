<?php

namespace app\controllers;

use Yii;
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
        /*$token = md5(time());
        $redisHelper = RedisHelper::getInstance();
        $redisHelper->set($token, 1);
        $redisHelper->expire($token, 60);*/
        return $this->render('chat', [
            'nickname' => '游客' . $this->uniqueId
        ]);
    }

    public function actionPush()
    {
        $content = Yii::$app->request->get('c','哈哈');
        var_dump(SwooleHelper::backendPush($content));
    }
}
