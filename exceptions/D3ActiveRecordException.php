<?php

namespace d3system\exceptions;

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
    public function __construct($model, string $flashMessage = 'Database error', string $message = '')
    {
        $message = 'Can\'t save ' . get_class($model) . PHP_EOL
            . ' Message: ' . ($message?:$flashMessage)  . PHP_EOL
            . ' Errors: ' .  VarDumper::export($model->getErrors()) . PHP_EOL
           .  ' Attributes: ' . VarDumper::export($model->attributes);

        \Yii::error($message, 'serverError');
        parent::__construct($flashMessage);
    }

}