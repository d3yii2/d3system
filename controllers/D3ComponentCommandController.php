<?php

namespace d3system\controllers;

use d3system\commands\D3CommandController;
use d3system\compnents\D3CommandComponent;
use Yii;
use yii\console\Exception;

/**
 * use for executing component commands. In command parameter add comma seperated component names
 * yii d3system/d3-component-command compnent1,compnent2
 */
class D3ComponentCommandController extends D3CommandController
{
    /**
     * execute command components
     * create extending from d3system\compnents\D3CommandComponent
     * @url  https://github.com/d3yii2/d3system/blob/master/README.md#compnentCommands
     * @param string $components coma separated component names
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\console\Exception
     */
    public function actionIndex(string $components)
    {
        foreach (explode(',', $components) as $componentName) {
            if (!Yii::$app->has($componentName)) {
                throw new Exception('Called undefined ' . $componentName);
            }
            $this->out('--- Run ' . $componentName . ' ---');
            /** @var D3CommandComponent $component */
            $component = Yii::$app->get($componentName);
            if (!in_array(D3CommandComponent::class, class_parents($component), true)) {
                throw new Exception(' In compnent ' . $componentName . ' not parent class D3ComponentCommandInterface');
            }
            if ($component->run($this)) {
                $this->out('--- Finished ' . $componentName . ' ---');
            } else {
                $errors = 'Errors:' . PHP_EOL . ' -'
                    . implode(PHP_EOL . ' -', $component->getErrors());
                $this->out(
                    $errors
                );
                Yii::error($errors);
            }
        }
    }
}