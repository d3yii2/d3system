<?php

namespace d3system\dictionaries;

use ReflectionMethod;
use Yii;
use d3system\models\SysModels;
use yii\helpers\ArrayHelper;
use d3system\exceptions\D3ActiveRecordException;
use yii\helpers\VarDumper;

class SysModelsDictionary{

    private const CACHE_KEY_LABEL_LIST = 'SysModelsDictionaryList';
    private const CACHE_KEY_CLASS_LIST = 'SysModelsDictionaryClassList';
    private const CACHE_KEY_TABLE_NAME_LIST = 'SysModelsDictionaryTableNameList';

    public static function getIdByClassName(string $className): int
    {
        $list = self::getClassList();
        if($id = (int)array_search($className, $list, true)){
            return $id;
        }

        if($model = SysModels::findOne(['class_name' => $className])){
            self::clearCache();
            return $model->id;
        }

        $model = new SysModels();
        if(method_exists($className,'tableName')) {
            $model->table_name = $className::tableName();
        }else{
            $model->table_name = '-';
        }
        $model->class_name = $className;
        if(!$model->save()){
            throw new D3ActiveRecordException($model,'','$list: ' .VarDumper::dumpAsString($list));
        }

        return $model->id;

    }

    public static function getLabelList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LABEL_LIST,
            static function () {
                $list = [];
                foreach(self::getClassList() as $id => $className){
                    if(!class_exists($className)
                        || !method_exists($className,'getLabel')
                        || !(new ReflectionMethod($className,'getLabel'))->isStatic()
                    ){
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
                        'name' => SORT_ASC,
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

    public static function getTableNameList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_TABLE_NAME_LIST,
            static function () {
                return ArrayHelper::map(
                    SysModels::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'table_name',
                    ])
                    ->orderBy([
                        'name' => SORT_ASC,
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
        Yii::$app->cache->delete(self::CACHE_KEY_LABEL_LIST);
        Yii::$app->cache->delete(self::CACHE_KEY_CLASS_LIST);
        Yii::$app->cache->delete(self::CACHE_KEY_TABLE_NAME_LIST);
    }
}
