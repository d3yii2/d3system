<?php

namespace d3system\exceptions;

use eaBlankonThema\components\FlashHelper;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

class D3ActiveRecordException extends Exception
{
    /**
     * D3ModelException constructor.
     * @param ActiveRecord $model
     * @param string $message
     * @param string $flashMessage
     */
    public function __construct(
        $model,
        string $flashMessage = 'Database error',
        string $message = '',
        bool $flashModelErrors = false
    )
    {
        $message = 'Can\'t save ' . get_class($model) . PHP_EOL
            . ' Message: ' . ($message?:$flashMessage)  . PHP_EOL
            . ' Errors: ' .  VarDumper::export($model->getErrors()) . PHP_EOL
           .  ' Attributes: ' . VarDumper::export($model->attributes);

        Yii::error($message, 'serverError');
        if($flashModelErrors){
            foreach ($model->getErrors() as $attribute => $attributeErrors){
                foreach($attributeErrors as $error){
                    FlashHelper::addWarning($model->getAttributeLabel($attribute) . ': ' . $error);
                    Yii::error($model->getAttributeLabel($attribute) . ': ' . $error, 'serverError');
                }
            }
        }
        parent::__construct($flashMessage);
    }

}