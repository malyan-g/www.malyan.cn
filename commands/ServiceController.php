<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/4/17
 * Time: 下午8:13
 */

namespace app\commands;


use app\components\helpers\SwooleHelper;
use yii\console\Controller;

class ServiceController extends Controller
{
    public function actionRun()
    {
        new SwooleHelper();
    }
}
