<?php

namespace d3system\managers;

use Yii;

class UrlManager extends \yii\web\UrlManager
{

    public $forwardGetParams = [];

    public function createUrl($params)
    {
        /**
         * get from request get forward parameters and add to url, if no parameter defined
         */
        foreach(array_intersect_key(Yii::$app->getRequest()->get(),array_flip($this->forwardGetParams)) as $paramName => $paramValue){
            if(!isset($params[$paramName])) {
                $params[$paramName] = $paramValue;
            }
        }
        return parent::createUrl($params);
    }

    public function isFancyBox(): bool
    {
        return (bool)array_intersect_key(Yii::$app->getRequest()->get(),array_flip($this->forwardGetParams));
    }

}