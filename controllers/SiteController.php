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
        if(Yii::$app->user->isGuest){
            return $this->redirect('http://passport.malyan.cn');
        }
        /*$token = md5(time());
        $redisHelper = RedisHelper::getInstance();
        $redisHelper->set($token, 1);
        $redisHelper->expire($token, 60);*/
        $nickname = !Yii::$app->user->isGuest ? Yii::$app->user->identity->nickname  : '游客' . uniqid();
        return $this->render('chat', [
            'nickname' => $nickname
        ]);
    }

    public function actionPush()
    {
        $content = Yii::$app->request->get('c','哈哈');
        var_dump(SwooleHelper::backendPush($content));
    }
}
