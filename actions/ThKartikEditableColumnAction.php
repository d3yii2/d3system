<?php

namespace d3system\actions;

use Exception;
use kartik\grid\EditableColumnAction;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;


/**
 *  use d3system\actions\D3EditableAction;
 *
 *  class SiteController extends Controller
 *  {
 *     public function actions()
 *     {
 *           return [
 *               'column-editable' => [
 *                   'class' => ThEditableColumnAction::class, // action class name
 *                   'modelClass' => PkPersonPlaygroundLimit::class,
 *                   'findModelControllerMethod' => 'findModelForEditable',
 *                    'canUpdateAttributes' => ['recomended']
 *                  ],
 *           ];
 *       }
 *  }
 *
 * // column
 * $columns = [
 *      [
 *          'class' => EditableColumn::class,
 *          'attribute' => 'recomended',
 *          'editableOptions' => [
 *          'inputType' => kartik\editable\Editable::INPUT_TEXT,
 *          'formOptions' => [
 *              'action' => Url::to([
 *                  'pk-person-playground-limit/column-recommended-update',
 *               ])
 *           ],
 *       ]
 * ];
 *
 */
class ThKartikEditableColumnAction extends EditableColumnAction
{
    /**
     * @var string|null controller method for find model record
     */
    public ?string $findModelControllerMethod = null;

    /**
     * @var array attributes, which can be updated
     */
    public array $canUpdateAttributes = [];

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function init()
    {
        if ($this->findModelControllerMethod) {
            $controller = $this->controller;
            $method = $this->findModelControllerMethod;
            $this->findModel = static function ($id)  use ($controller, $method) {
                return $controller->$method($id);
            };
        }
        if ($this->canUpdateAttributes) {
            $post = Yii::$app->request->post();
            $attributeName = ArrayHelper::getValue($post, 'editableAttribute');
            if (!in_array($attributeName, $this->canUpdateAttributes, true)) {
                throw new \yii\base\Exception('Illegal request');
            }
        }
        parent::init();
    }
}
