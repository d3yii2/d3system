<?php

namespace d3system\exceptions;

use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * Exception for catching active record errors
 * Class D3ActiveRecordException
 * @package d3system\exceptions
 * @deprecated use d3system\exceptions\D3ActiveRecordException2
 */
class D3ActiveRecordException extends Exception
{
    public object $model;

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
        ?string $flashMessage = null,
        bool $loggingMessage = true,
        $flashAttributes = false,
        string $errorCategory = 'application'
    ) {
        $this->model = $model;
        $modelErrors = 'Can\'t save ' . get_class($model);
        $attributesError = [];
        foreach ($model->getErrors() as $attribute => $attributeErrors) {
            $attributeLabel = $model->getAttributeLabel($attribute);
            foreach ($attributeErrors as $error) {
                $attributesError[] = $attributeLabel . ': "' . $error . '"';
            }
        }
        $modelErrors .= PHP_EOL . implode(PHP_EOL, $attributesError);

        parent::__construct($modelErrors);
    }

    public function getExtraData(): array
    {
        return [
            'modelClassName' => get_class($this->model),
            'errors' => $this->model->getErrors(),
            'attributes' => $this->model->attributes,
        ];
    }

    public function __toString(): string
    {
        return parent::__toString() . PHP_EOL
            . VarDumper::dumpAsString($this->getExtraData());
    }
}
