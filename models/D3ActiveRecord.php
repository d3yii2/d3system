<?php

namespace d3system\models;

use d3system\exceptions\D3ActiveRecordException;
use eaBlankonThema\components\FlashHelper;
use d3system\exceptions\D3Exception;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * Class D3ActiveRecord
 * @package d3system\models
 */
class D3ActiveRecord extends ActiveRecord
{
    /**
     * @param $data
     * @param null $formName
     * @throws Exception
     */
    public function loadStrict($data, $formName = null)
    {
        if (!parent::load($data, $formName)) {
            throw new D3Exception(
                \Yii::t('d3system', 'Unexpected Server Error'),
                'Cannot load data into model: ' . static::class . PHP_EOL
                . PHP_EOL . 'Data: ' . VarDumper::dumpAsString($data)
                . PHP_EOL . ' Errors: ' .  VarDumper::export($this->getErrors())
                . PHP_EOL .  ' Attributes: ' . VarDumper::export($this->attributes)
            );
        }
    }

    /**
     * @param string $flashMessage
     * @param string $logMessage
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws D3ActiveRecordException
     */
    public function saveStrict(
        string $flashMessage = '',
        string $logMessage = '',
        bool $runValidation = true,
        $attributeNames = null
    ) {
        if (!$this->save($runValidation, $attributeNames)) {
            throw new D3ActiveRecordException($this, $flashMessage, $logMessage);
        }
        return true;
    }
}