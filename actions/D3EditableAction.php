<?php

declare(strict_types=1);

namespace d3system\actions;

use ReflectionMethod;
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
use function class_exists;
use function dd;
use function implode;
use function method_exists;

use const PHP_EOL;

class D3EditableAction extends Action
{
    /**
     * @var object $controller
     */
    public $controller;

    /**
     * @var int $id
     */
    public $id;

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
     * @var string
     */
    private $methodName = 'findModel';

    /**
     * @param int $id
     * @return array|bool
     * @throws HttpException
     */
    final public function run(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request                    = Yii::$app->request;

        /**
         * check does method exist
         * fails if findModel is procted MUST BE Public function inside controller
         */
        if (method_exists($this->controller, $this->methodName)) {
            $reflection = new ReflectionMethod($this->controller, $this->methodName);
            if (!$reflection->isPublic()) {
                throw new HttpException(405, "The called {$this->methodName} method is not public.");
            }
        }

        $requestPost = $request->post();
        // Check if there is an Editable ajax request
        if (!$request->post('hasEditable')) {
            return $this->cannotUpdate();
        }
        unset($requestPost['hasEditable']);

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
         * Invalid Request
         */
        $getUserInvalidRequest = $this->filterRequestPost($requestPost, $getUserInvalidAttributes);

        /**
         * Remove Invalid User Attributes
         */
        $getFilteredRequest = $this->filterRequest($requestPost, $getUserInvalidRequest);

        $getWhiteListAttributes = $this->getWhiteListAttributes($getFilteredRequest);

        $getWhiteListRequest = $this->filterRequestPost($requestPost, $getWhiteListAttributes);

        $post = [];
        foreach ($getWhiteListRequest as $name => $value) {
            $post[$name] = $value;
        }

        // use Yii's response format to encode output as JSON

        if (!$post) {
            return $this->cannotUpdate();
        }

        /**
         * @var Model $model
         */
        $model = $this->controller->{$this->methodName}($id);
        foreach ($post as $name => $value) {
            if (!$model->isAttributeSafe($name)) {
                return $this->cannotUpdate();
            }
        }
        $model->setAttributes($post);

        if ($model->save()) {
            // read or convert your posted information
            $value = $model->$name;
            // return JSON encoded output in the below format
            return [
                'output'  => $value,
                'message' => ''
            ];
        }

        $errors = [];
        foreach ($model->errors as $field => $messages) {
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
     * @param array $request
     * @param array $getOptional
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
        if (!class_exists($this->modelName)) {
            throw new HttpException(404, Yii::t('crud', 'The requested page does not exist.'));
        }

        if (($model = $this->modelName::findOne($id)) === null) {
            throw new HttpException(404, Yii::t('crud', 'The requested page does not exist.'));
        }
        return $model;
    }

    public function cannotUpdate(): array
    {
        return [
            'output'  => '',
            'message' => Yii::t(
                'd3system',
                'Cannot update this field'
            )
        ];
    }
}
