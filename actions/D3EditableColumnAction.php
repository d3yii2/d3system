<?php

namespace d3system\actions;

use Exception;
use kartik\grid\EditableColumnAction;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\HttpException;


class D3EditableColumnAction extends EditableColumnAction
{

    /**
     * @var object $controller
     */
    public $controller;

   /**
     * @var array
     */
    public $editAbleFieldForbiddenDefault = [];

    /**
     * @var array
     */
    public $editAbleFields = [];

    /**
     * Forbidden has higher priority then editAbleFields
     *
     * @var array
     */
    public $editAbleFieldsForbidden = [];

    /**
     * @var string
     */
    public $methodName = 'findModel';


    /**
     * @var null|callable callable for processing own update or insert
     */
    public $processCallable;

    public function validateEditable()
    {
        try {
            $requestPost = Yii::$app->request->post();
            // Check if there is an Editable ajax request
            if (!isset($requestPost['hasEditable'])) {
                return $this->cannotUpdate();
            }
            $key = ArrayHelper::getValue($requestPost, 'editableKey');
            if (!ctype_digit($key)) {
                $key = Json::decode($key);
            }
            $modelRecord = $this->findD3Model($key);
            $model = new $this->modelClass();

            if ($this->processCallable) {
                /** for save primary key fields no forbidden*/
                $forbiddenFields = array_merge(
                    $this->editAbleFieldForbiddenDefault,
                    $this->editAbleFieldsForbidden
                );
            } else {
                $forbiddenFields = array_merge(
                    $model::primaryKey(),
                    $this->editAbleFieldForbiddenDefault,
                    $this->editAbleFieldsForbidden
                );
            }
            $editableAttribute = $requestPost['editableAttribute'];


            if($this->editAbleFields && !in_array($editableAttribute,$this->editAbleFields,true)){
                return $this->cannotUpdate();
            }
            if(in_array($editableAttribute,$forbiddenFields,true)){
                return $this->cannotUpdate();
            }
            if (!$model->isAttributeSafe($editableAttribute)) {
                return $this->cannotUpdate();
            }
            if ($this->processCallable && is_callable($this->processCallable, true)) {
                return call_user_func($this->processCallable, $modelRecord, $requestPost, $key);
            }

            return parent::validateEditable();
        } catch (Exception $e) {
            return ['output' => '', 'message' => $e->getMessage()];
        }
    }

    public function cannotUpdate(): array
    {
        return [
            'output'  => '',
            'message' => Yii::t(
                'd3system',
                'Cannot update this field.'
            )
        ];
    }

    /**
     * Finds the CwpalletPallet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer|array $id
     * @return object the loaded model
     * @throws HttpException if the model cannot be found
     */
    public function findD3Model($id): ?object
    {

        if (method_exists($this->controller, $this->methodName)) {
            return $this->controller->{$this->methodName}($id);
        }

        if (!class_exists($this->modelClass)) {
            throw new HttpException(404, Yii::t('crud', 'Cannot update this field.'));
        }

        if (($model = $this->modelClass::findOne($id)) === null) {
            throw new HttpException(404, Yii::t('crud', 'Cannot update this field.'));
        }
        return $model;
    }
}