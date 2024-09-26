<?php

declare(strict_types=1);

namespace d3system\actions;

use d3system\helpers\FlashHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii2mod\settings\actions\SettingsAction;
use yii2mod\settings\events\FormEvent;

class D3SettingAction extends SettingsAction
{
    /**
     * @var mixed|string
     */
    private $addSectionKey;

    /**
     * Renders the settings form.
     *
     * @param string $addSectionKey
     * @return string
     * @throws InvalidConfigException
     */
    public function run(string $addSectionKey = '')
    {
        $this->addSectionKey = $addSectionKey;
        /* @var $model Model */
        $model = Yii::createObject($this->modelClass);
        if($addSectionKey){
            $model->addSectionKey = $addSectionKey;
        }
        $event = Yii::createObject(['class' => FormEvent::class, 'form' => $model]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->trigger(self::EVENT_BEFORE_SAVE, $event);

            $this->saveSettings($model);

            $this->trigger(self::EVENT_AFTER_SAVE, $event);

            if ($this->successMessage !== null) {
                FlashHelper::addSuccess($this->successMessage);
            }

            return $this->controller->refresh();
        }

        $this->prepareModel($model);

        return $this->controller->render($this->view, ArrayHelper::merge($this->viewParams, [
            'model' => $model,
        ]));
    }

    /**
     * @param Model $model
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function getSection(Model $model): string
    {
        if(method_exists($model,'getSectionName')){
            return $model->getSectionName($this->addSectionKey);
        }
        if ($this->sectionName !== null) {
            return $this->sectionName;
        }

        return $model->formName();
    }

}
