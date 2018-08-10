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
    public function actionIndex()
    {

        return $this->render('index');
    }

    /**
     * 聊天
     * @return string
     */
    public function actionHome()
    {

        return $this->render('home');
    }

    public function actionPush()
    {
        $content = Yii::$app->request->get('c','哈哈');
        var_dump(SwooleHelper::backendPush($content));
    }
}
