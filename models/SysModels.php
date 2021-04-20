<?php

namespace d3system\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3system\yii2\db\D3Db;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "sys_models".
 *
 * @property int $id
 * @property string $table_name Table
 * @property string $class_name Class
 */
class SysModels extends ActiveRecord
{

    public static function getDb()
    {
        return D3Db::clone();
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'sys_models';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['table_name'], 'required'],
            [['table_name', 'class_name'], 'unique', 'targetAttribute' => ['table_name', 'class_name']],
            [['table_name', 'class_name'], 'string', 'max' => 256],
        ];
    }

    public static function getTableNameIdList(string $cacheKey, int $cacheDuration): array
    {

        $data = self::find()->asArray()->all();

        $list =  ArrayHelper::map($data,'class_name','id');
        Yii::$app->cache->set($cacheKey.'ByClassName', $list, $cacheDuration);

        $list =  ArrayHelper::map($data,'table_name','id');
        Yii::$app->cache->set($cacheKey.'ByTableName', $list, $cacheDuration);

        return $list;
    }

    public static function getClassNameIdList(string $cacheKey, int $cacheDuration): array
    {

        $list =  ArrayHelper::map(self::find()->asArray()->all(),'table_name','id');
        Yii::$app->cache->set($cacheKey, $list, $cacheDuration);
        return $list;
    }


    /**
     * @param ActiveRecord|object $model
     * @throws D3ActiveRecordException
     */
    public static function addRecord(object $model): void
    {
        $record = new self();
        $record->table_name = $model->tableName();
        $record->class_name = get_class($model);
        if(!$record->save()){
            throw new D3ActiveRecordException($record);
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        SysModelsDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        SysModelsDictionary::clearCache();
    }

}
