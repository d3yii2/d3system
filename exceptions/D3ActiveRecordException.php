<?php

namespace d3system\exceptions;

use d3system\helpers\FlashHelper;
use Yii;
use yii\base\Exception;
use yii\console\Application;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * Exception for logging and displaying in flash active record errors
 * Class D3ActiveRecordException
 * @package d3system\exceptions
 */
class D3ActiveRecordException extends Exception
{
    /**
     * D3ModelException constructor.
     * @param ActiveRecord|Object $model
     * @param string|null $flashMessage message for displaying in flash
     * @param bool|false $loggingMessage logging message. If false, do not log
     * @param array|bool $flashAttributes list attributes for displaying in flash. If false, do not show. If true, show all
     * @param string $errorCategory
     */
    public function __construct(
        $model,
        string $flashMessage = null,
        bool $loggingMessage = true,
        $flashAttributes = false,
        string $errorCategory = ''
    ) {
        if (!$errorCategory) {
            $errorCategory = 'application';
        }

        $modelErrors = 'Can\'t save ' . get_class($model);
        if ($flashMessage) {
            $modelErrors .=  PHP_EOL . ' Flash Message: ' . $flashMessage;
        }

        $modelErrors .= PHP_EOL
            . ' Errors: ' . VarDumper::export($model->getErrors()) . PHP_EOL
            . ' Attributes: ' . VarDumper::export($model->attributes);
        if ($flashMessage) {
            $modelErrors .= PHP_EOL . 'flashMessage: ' . $flashMessage;
        }
        if ($loggingMessage !== false) {
            Yii::error($modelErrors, $errorCategory);
        }

        $isConsole = Yii::$app instanceof Application;
        if ($isConsole) {
            echo $modelErrors . PHP_EOL;
        }

        if ($flashMessage) {
            FlashHelper::addWarning($flashMessage);
        }
        if ($flashAttributes && !$isConsole) {
            foreach ($model->getErrors() as $attribute => $attributeErrors) {
                if ($flashAttributes === true || in_array($attribute, $flashAttributes, true)) {
                    $attributeLabel = $model->getAttributeLabel($attribute);
                    foreach ($attributeErrors as $error) {
                        $attributeMessage = $attributeLabel . ':' . $error;
                        if (!$flashMessage) {
                            $flashMessage = $attributeMessage;
                        }
                        FlashHelper::addWarning($attributeMessage);
                    }
                }
            }
        }

        if (!$flashMessage) {
            $flashMessage = Yii::t('d3system', 'Database error');
        }
        parent::__construct($flashMessage);
    }
}
