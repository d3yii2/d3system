<?php

namespace d3system\models;

use d3system\exceptions\D3ActiveRecordException;
use d3system\models\base\SysCronFinalPoint as BaseSysCronFinalPoint;

/**
 * This is the model class for table "sys_cron_final_point".
 */
class SysCronFinalPoint extends BaseSysCronFinalPoint
{
    public static function getFinalPointValue(string $route, $key = null): int
    {
        return (int)self::find()
            ->select('value')
            ->where(['route' => $route])
            ->where(['key' => $key])
            ->scalar();
    }

    public static function getFinalPointValueAsString(string $route, $key = null): string
    {
        return (string)self::find()
            ->select('value')
            ->where(['route' => $route])
            ->where(['key' => $key])
            ->scalar();
    }

    public static function saveFinalPointValue(string $route, $value, $key = null): void
    {
        if(!$model = self::find()
            ->where(['route'=>$route])
            ->where(['key' => $key])
            ->one()
        ){
            $model = new self();
            $model->route = $route;
            $model->key = (string)$key;
        }
        $model->value = $value;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }
    }


}

