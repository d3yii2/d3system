<?php

namespace d3system\exceptions;

use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * Exception class for handling errors related to ActiveRecord model operations.
 * Added extra data for debug and Sentry.
 */
class D3ActiveRecordException2 extends Exception
{
    public object $model;

    /**
     * D3ModelException constructor.
     * @param ActiveRecord|Object $model
     */
    public function __construct(
        object $model
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
