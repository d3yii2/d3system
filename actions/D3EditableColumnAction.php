<?php


namespace d3system\actions;


use d3system\exceptions\D3UserAlertException;
use kartik\grid\EditableColumnAction;
use Yii;
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

    public function run()
    {
        $requestPost = Yii::$app->request->post();
        // Check if there is an Editable ajax request
        if (!isset($requestPost['hasEditable'])) {
            return $this->cannotUpdate();
        }
        $id = $requestPost['editableKey'];
        $model = $this->findD3Model($id);
        $forbiddenFields = array_merge(
            $model::primaryKey(),
            $this->editAbleFieldForbiddenDefault,
            $this->editAbleFieldsForbidden
        );
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

        return parent::run();
    }

    public function validateEditable()
    {
        try {
            return parent::validateEditable();
        } catch (D3UserAlertException $e) {
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
     * @param integer $id
     * @return object the loaded model
     * @throws HttpException if the model cannot be found
     */
    public function findD3Model(int $id): object
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