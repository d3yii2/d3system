<?php

namespace d3system\dictionaries;

use Yii;
use d3system\models\SysModels;
use yii\helpers\ArrayHelper;
use d3system\exceptions\D3ActiveRecordException;

class SysModelsDictionary{

    private const CACHE_KEY_LIST = 'SysModelsDictionaryList';
    private const CACHE_KEY_CLASS_LIST = 'SysModelsDictionaryClassList';

    public static function getIdByName(string $name): int
    {
        $list = self::getList();
        if($id = (int)array_search($name, $list, true)){
            return $id;
        }
        $model = new SysModels();
        $model->name = $name;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }

        return $model->id;

    }

    public static function getList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST,
            static function () {
                $list = [];
                foreach(self::getClassList() as $id => $className){
                    if(!class_exists($className) || !method_exists($className,'getLabel')){
                        $path = explode('\\', $className);
                        $list[$id] = array_pop($path);
                        continue;
                    }
                    $list[$id] = $className::getLabel();
                }
                return $list;
            }
        );
    }

    public static function getClassList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_CLASS_LIST,
            static function () {
                return ArrayHelper::map(
                    SysModels::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'class_name',
                    ])
                    ->orderBy([
                        'id' => SORT_ASC,
                    ])
                    ->asArray()
                    ->all()
                ,
                'id',
                'name'
                );
            }
        );
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST);
    }
}
