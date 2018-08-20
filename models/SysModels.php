<?php

namespace d3system\models;

use Yii;

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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'table_name' => 'Table Name',
            'class_name' => 'Class Name',
        ];
    }
}
