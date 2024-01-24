<?php

namespace d3system\controllers;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;

class ErrorController extends Controller
{
    public function actionIndex()
    {

        $message = null;
        if ($exception = Yii::$app->errorHandler->exception) {
            $message = $exception->getMessage();
//            echo VarDumper::dumpAsString($exception->getMessage());
//            echo VarDumper::dumpAsString($exception->getTraceAsString());
        }

        switch (Yii::$app->response->statusCode) {
            case 403:
                return $this->render('error-403', ['message' => $message]);

            case 404:
                return $this->render('error-404');

            case 500:
                return $this->render('error-500');

            default:
                return $this->render('error-other');
        }
    }
}
