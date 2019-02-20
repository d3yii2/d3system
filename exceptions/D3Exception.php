<?php

namespace d3system\exceptions;

use yii\base\Exception;

class D3Exception extends Exception
{
    /**
     * D3ModelException constructor.
     * @param string $message
     * @param string $flashMessage
     */
    public function __construct(string $flashMessage = 'System error', string $message = '')
    {
        $message = ' Message: ' . ($message?:$flashMessage);

        \Yii::error($message, 'serverError');
        parent::__construct($flashMessage);
    }

}