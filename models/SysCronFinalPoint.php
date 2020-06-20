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
        $where = ['route' => $route];
        if($key !== null){
            $where['key'] = $key;
        }

        return (int)self::find()
            ->select('value')
            ->where($where)
            ->scalar();
    }

    public static function getFinalPointValueAsString(string $route, $key = null): string
    {
        $where = ['route' => $route];
        if($key !== null){
            $where['key'] = $key;
        }
        return (string)self::find()
            ->select('value')
            ->where($where)
            ->scalar();
    }

    public static function saveFinalPointValue(string $route, $value, $key = null): void
    {
        $where = ['route' => $route];
        if($key !== null){
            $where['key'] = $key;
        }

        if(!$model = self::find()
            ->where($where)
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

