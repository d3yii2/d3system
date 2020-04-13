<?php

namespace d3system\models;

use d3system\exceptions\D3ActiveRecordException;
use d3system\models\base\SysCronFinalPoint as BaseSysCronFinalPoint;

/**
 * This is the model class for table "sys_cron_final_point".
 */
class SysCronFinalPoint extends BaseSysCronFinalPoint
{
    public static function getFinalPointValue(string $route): int
    {
        return (int)self::find()
            ->select('value')
            ->where(['route'=>$route])
            ->scalar();
    }

    public static function saveFinalPointValue(string $route, $value): void
    {
        if(!$model = self::find()
            ->select('value')
            ->where(['route'=>$route])
            ->scalar()
        ){
            $model = new self();
            $model->route = $route;
        }
        $model->value = $value;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }
    }


}
