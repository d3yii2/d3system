<?php

namespace d3system\compnents;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;


/**
 * Use for getting data from other models
 * 1. In config define as module component with tasks
 * 'components' => [
 *   'd3caller' => [
 *     'class' => 'd3system\compnents\D3Caller',
 *     'tasks' => [
 *        'itemsLoadList' => [  // itemsLoadList - caller name
 *           [
 *             'moduleName' => 'app',
 *             'componentName' => 'itemsLoadList',
 *             'componentMethod' => 'createLists'
 *          ],
 *       ],
 *    ]
 *  ]
 * 2. in controllers actions  add data collection
 * $module = $this->module;
 * $items = [];
 * if ($module->has('d3caller')) {
 *   $items = $module->d3caller->run(
 *     'itemsLoadList', // caller name
 *     [], // component parameters
 *      //method parameters
 *     [
 *        'invoiceSysModelId' => $sysId,
 *        'invoiceId' => $model->id,
 *        'items' => $items,
 *     ]
 *   );
 * }
 *
 */
class D3Caller extends Component
{
    public array $tasks = [];

    /**
     * Executes a specified task with the given parameters and arguments, and optionally transforms the results
     * using the provided class name.
     *
     * @param string $taskName The name of the task to execute.
     * @param array $parameters Associative array of parameters to set on the component before execution.
     * @param array $args Arguments to pass to the component's run method.
     * @param string|null $className Optional class name to transform each returned row into objects of the specified class.
     * @return array The processed data resulting from the execution of the specified task.
     * @throws InvalidConfigException If the specified module or component cannot be found.
     */
    public function run(
        string $taskName,
        array  $parameters = [],
        array  $args = [],
        string $className = null
    ): array
    {
        $data = [];
        foreach ($this->tasks[$taskName] ?? [] as $task) {
            if (!$module = Yii::$app->getModule($task['moduleName'])) {
                throw new InvalidConfigException('Module "' . $task['moduleName'] . '" not found');
            }
            if (!$component = $module->get($task['componentName'])) {
                throw new InvalidConfigException('Component "' . $task['componentName'] . '" not found');
            }
            foreach ($task['componentParameters'] ?? [] as $parameterName => $parameterValue) {
                $component->$parameterName = $parameterValue;
            }
            foreach ($parameters as $parameterName => $parameterValue) {
                $component->$parameterName = $parameterValue;
            }
            $taskMethodName = $task['componentMethod']??'run';
            foreach (call_user_func_array([$component,$taskMethodName],$args) as $row) {
                if (!$className) {
                    $data[] = $row;
                    continue;
                }
                $data[] = new $className($row);
            }
        }
        return $data;
    }
}
