<?php

namespace d3system\actions;

use Closure;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\base\Model;
use Yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;


/**
 * Class EditableAction
 *
 * @package yii2mod\editable
 */
class Yii2ModEditableAction extends Action
{
    /**
     * @var string the class name to handle
     */
    public $modelClass;

    /**
     * @var string the scenario to be used (optional)
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * @var Closure a function to be called previous saving model. The anonymous function is preferable to have the
     * model passed by reference. This is useful when we need to set model with extra data previous update
     */
    public $preProcess;

    /**
     * @var Closure a function to be called for preparing post value. return prepared value
     */
    public $prepareValue;

    /**
     * @var bool whether to create a model if a primary key parameter was not found
     */
    public $forceCreate = false;

    /**
     * @var string default pk column name
     */
    public $pkColumn = 'id';

    /**
     * @var string
     */
    public $methodName = 'findModel';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
    }

    /**
     * Runs the action
     *
     * @return bool|array
     *
     * @throws BadRequestHttpException
     */
    public function run()
    {
        $model = $this->findModelOrCreate();
        $attribute = $this->getModelAttribute();

        if ($this->preProcess && is_callable($this->preProcess, true)) {
            call_user_func($this->preProcess, $model);
        }

        $model->setScenario($this->scenario);

        $model->$attribute = $this->readValue();

        if ($model->validate([$attribute])) {
            return $model->save(false);
        }

        Yii::$app->response->statusCode = 400;
        return  $model->getFirstError($attribute);

    }

    /**
     * @return array|mixed
     *
     * @throws BadRequestHttpException
     */
    private function getModelAttribute()
    {
        $attribute = Yii::$app->request->post('name');

        if (strpos($attribute, '.')) {
            $attributeParts = explode('.', $attribute);
            $attribute = array_pop($attributeParts);
        }

        if ($attribute === null) {
            throw new BadRequestHttpException('Attribute cannot be empty.');
        }

        return $attribute;
    }

    /**
     * @return ActiveRecord
     *
     * @throws BadRequestHttpException
     */
    private function findModelOrCreate(): ActiveRecord
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;

        $pk = unserialize(base64_decode(Yii::$app->request->post('pk')),['allowed_classes'=> false]);
        if (method_exists($this->controller, $this->methodName)) {
            $model = $this->controller->{$this->methodName}($pk);
        }else {
            $model = $class::findOne(is_array($pk) ? $pk : [$this->pkColumn => $pk]);
        }

        if (!$model) {
            if ($this->forceCreate) {
                $model = new $class();
            } else {
                throw new BadRequestHttpException('Entity not found by primary key ' . $pk);
            }
        }

        return $model;
    }


    private function readValue()
    {
        $value = Yii::$app->request->post('value');
        if ($this->prepareValue && is_callable($this->prepareValue, true)) {
            $value = call_user_func($this->prepareValue, $value);
        }
        return $value;
    }

}
