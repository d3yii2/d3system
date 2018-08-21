<?php

namespace d3system\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "sys_models".
 *
 * @property int $id
 * @property string $table_name Table
 * @property string $class_name Class
 */
class SysModels extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
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
            [['table_name', 'class_name'], 'string', 'max' => 256],
        ];
    }

    public static function getTableNameIdList(string $cacheKey, int $cacheDuration): array
    {

        $list =  ArrayHelper::map(self::findAll(),'table_name','id');
        \Yii::$app->cache->set($cacheKey, $list, $cacheDuration);
        return $list;
    }

    public static function addRecord($model)
    {
        $record = new self();
        $record->table_name = $model->tableName();
        $record->class_name = get_class($model);
        $record->save();
    }

}
