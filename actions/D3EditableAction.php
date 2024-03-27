<?php

declare(strict_types=1);

namespace d3system\actions;

use Closure;
use d3system\exceptions\D3UserAlertException;
use Yii;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\Response;

use function class_exists;
use function implode;
use function method_exists;

class D3EditableAction extends Action
{
    /**
     * @var object $controller
     */
    public $controller;

    public array $editAbleFieldForbiddenDefault = [];

    public array $editAbleFields = [];

    /**
     * Forbidden has higher priority then editAbleFields
     */
    public array $editAbleFieldsForbidden = [];

    public ?string $modelName = null;

    public string $methodName = 'findModel';

    /**
     * @var Closure a function to be called previous saving model. The anonymous function is preferable to have the
     * model passed by reference. This is useful when we need to set model with extra data previous update
     */
    public $preProcess;

    /**
     * @var Closure
     */
    public $outPreProcess;

    /**
     * @param int $id
     * @return array
     * @throws HttpException
     */
    public function run(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request                    = Yii::$app->request;
        $errors = [];

        $requestPost = $request->post();
        // Check if there is an Editable ajax request
        if (!$request->post('hasEditable')) {
            return $this->cannotUpdate();
        }
        unset($requestPost['hasEditable']);

        /**
         * @var ActiveRecord $model
         */
        $model = $this->findModel($id);

        $forbiddenFields = array_merge(
            $model::primaryKey(),
            $this->editAbleFieldForbiddenDefault,
            $this->editAbleFieldsForbidden
        );

        $value = reset($requestPost);
        if (is_array($value)) {
            $requestPost = $value;
        }
        foreach ($requestPost as $name => $value) {

            if($this->editAbleFields && !in_array($name,$this->editAbleFields,true)){
                return $this->cannotUpdate();
            }
            if(in_array($name,$forbiddenFields,true)){
                return $this->cannotUpdate();
            }
            if (!$model->isAttributeSafe($name)) {
                return $this->cannotUpdate();
            }
        }

        $model->setAttributes($requestPost);

        try {
            if ($this->preProcess && is_callable($this->preProcess, true)) {
                call_user_func($this->preProcess, $model);
            }

            if ($model->save()) {
                $this->afterSave($model,$requestPost);
                // read or convert your posted information
                $output =[];
                foreach ($requestPost as $name => $value) {
                    $output[$name] = $model->$name;
                }
                if ($this->outPreProcess && is_callable($this->outPreProcess, true)) {
                    $output = call_user_func($this->outPreProcess, $model, $output, $name);
                }
                if(count($output) === 1){
                    $output = array_values($output)[0];
                }
                // return JSON encoded output in the below format
                return [
                    'output'  => $output,
                    'message' => ''
                ];
            }
        } catch (D3UserAlertException $e) {
            $errors[] = $e->getMessage();
        }


        foreach ($model->errors as $field => $messages) {
            if (isset($requestPost[$field])) {
                return [
                    'output'  => '',
                    'message' => implode('; ', $messages)
                ];
            }
            foreach ($messages as $message) {
                $errors[] = $model->getAttributeLabel($field)
                    . ': '
                    . $message;
            }
        }
        return [
            'output'  => '',
            'message' => implode('<br>', $errors)
        ];
    }

    /**
     * Finds the CwpalletPallet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return object the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel(int $id)
    {

        if (method_exists($this->controller, $this->methodName)) {
            return $this->controller->{$this->methodName}($id);
        }

        if (!class_exists($this->modelName)) {
            throw new HttpException(404, Yii::t('crud', 'Cannot update this field.'));
        }

        if (($model = $this->modelName::findOne($id)) === null) {
            throw new HttpException(404, Yii::t('crud', 'Cannot update this field.'));
        }
        return $model;
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

    public function afterSave($model, array $requestPost): void
    {

    }
}
