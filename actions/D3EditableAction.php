<?php

declare(strict_types=1);

namespace d3system\actions;

use Yii;
use yii\base\Action;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\Response;

use function array_diff;
use function array_diff_assoc;
use function array_keys;
use function array_merge;
use function array_unique;

use const PHP_EOL;

class D3EditableAction extends Action
{
    /**
     * @var array
     */
    private $editAbleFieldForbbidenDefault = [
        'id'
    ];

    /**
     * @var array
     */
    public $editAbleFields = [];

    /**
     * Forbidden has higher priority then editAbleFields
     *
     * @var array
     */
    public $editAbleFieldsForbbiden = [];

    /**
     * @var string
     */
    public $modelName;

    /**
     * @param int $id
     * @return array|bool
     */
    final public function run(int $id)
    {
        $request = Yii::$app->request;

        $requestPost = $request->post();

        /**
         * Invalid Attributes
         */
        $getUserInvalidAttributes = $this->getAttributes($requestPost, $this->editAbleFields);

        if ($getUserInvalidAttributes) {
            Yii::error(
                'd3EditableAction User Invalid Attributes' . PHP_EOL . VarDumper::export($getUserInvalidAttributes)
            );
        }

        /**
         * Invalid Requrest
         */
        $getUserInvalidRequest = $this->filterRequestPost($requestPost, $getUserInvalidAttributes);

        /**
         * Remove Invalid User Attributes
         */
        $getFilteredRequest = $this->filterRequest($requestPost, $getUserInvalidRequest);

        $getWhiteListAttributes = $this->getWhiteListAttributes($getFilteredRequest);

        $getWhiteListRequest = $this->filterRequestPost($requestPost, $getWhiteListAttributes);

        // Check if there is an Editable ajax request
        if (!$request->post('hasEditable')) {
            return false;
        }

        $post = [];
        foreach ($getWhiteListRequest as $name => $value) {
            $post[$name] = $value;
        }

        // use Yii's response format to encode output as JSON
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$post) {
            return [
                'output'  => '',
                'message' => Yii::t(
                    'cm_delivery',
                    'Cannot update this field'
                )
            ];
        }

        /**
         * @var Model $model
         */
        $model = $this->findModel($id);
        $model->setAttributes($post);
        // read your posted model attributes
        if ($model->save()) {
            // read or convert your posted information
            $value = $model->$name;

            // return JSON encoded output in the below format
            return ['output' => $value, 'message' => ''];

            // alternatively you can return a validation error
            // return ['output'=>'', 'message'=>Yii::t('cm_delivery', 'Validation error')];
        }
        // else if nothing to do always return an empty JSON encoded output

        //  return ['output'=>'', 'message'=>''];
        $errors = [];
        foreach ($model->errors as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = $model->getAttributeLabel($field)
                    . ': '
                    . $message;
            }
        }
        return ['output' => '', 'message' => implode('<br>', $errors)];
    }

    /**
     * @param array $request
     * @return array
     */
    private function getAttributes(array $request, $getOptional = []): array
    {
        return array_diff(
            array_keys($request),
            array_merge(
                $this->editAbleFieldsForbbiden,
                $this->editAbleFieldForbbidenDefault
            ),
            $getOptional
        );
    }

    /**
     * @param $request
     * @param $getPost
     * @return array
     */
    private function filterRequest($request, $getPost): array
    {
        return array_diff_assoc($request, $getPost);
    }

    /**
     * @param array $request
     * @param array $getAttributes
     * @return array
     */
    private function filterRequestPost(array $request, array $getAttributes): array
    {
        return ArrayHelper::filter($request, $getAttributes);
    }

    /**
     * @param array $getFilteredRequest
     * @return array
     */
    private function getWhiteListAttributes($getFilteredRequest): array
    {
        $getBaseWhiteListAttributes = array_unique(
            array_merge(
                array_keys($getFilteredRequest),
                $this->editAbleFields
            )
        );

        return array_diff(
            $getBaseWhiteListAttributes,
            array_merge(
                $this->editAbleFieldsForbbiden,
                $this->editAbleFieldForbbidenDefault
            )
        );
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
        if (($model = $this->modelName::findOne($id)) === null) {
            throw new HttpException(404, Yii::t('crud', 'The requested page does not exist.'));
        }
        return $model;
    }
}